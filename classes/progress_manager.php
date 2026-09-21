<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_videopredict;

/**
 * Progress and grade calculations.
 *
 * @package mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_manager {
    /**
     * Merge a watched segment into canonical non-overlapping segments.
     */
    public function merge_segments(array $segments, float $start, float $end): array {
        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }
        $start = max(0, $start);
        $end = max($start, $end);
        if (($end - $start) > 20) {
            $end = $start + 20;
        }
        $segments[] = [$start, $end];
        usort($segments, static fn($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($segments as $segment) {
            if (!is_array($segment) || count($segment) < 2) {
                continue;
            }
            $s = max(0, (float)$segment[0]);
            $e = max($s, (float)$segment[1]);
            if (!$merged || $s > $merged[count($merged) - 1][1] + 1.0) {
                $merged[] = [$s, $e];
            } else {
                $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $e);
            }
        }
        return $merged;
    }

    /**
     * Total unique watched seconds.
     */
    public function unique_seconds(array $segments): float {
        $total = 0.0;
        foreach ($segments as $segment) {
            $total += max(0, (float)$segment[1] - (float)$segment[0]);
        }
        return $total;
    }

    /**
     * Get or create progress.
     */
    public function get_progress(int $activityid, int $userid): \stdClass {
        global $DB;
        $record = $DB->get_record('videopredict_progress', [
            'videopredictid' => $activityid,
            'userid' => $userid,
        ]);
        if ($record) {
            return $record;
        }
        return (object)[
            'id' => 0,
            'videopredictid' => $activityid,
            'userid' => $userid,
            'duration' => 0,
            'lastposition' => 0,
            'maxwatched' => 0,
            'uniquewatched' => 0,
            'totalwatchtime' => 0,
            'percent' => 0,
            'segments' => '[]',
            'completed' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
    }

    /**
     * Maximum server-approved seek position.
     */
    public function max_allowed_position(\stdClass $activity, int $userid, \stdClass $progress): float {
        global $DB;
        $limit = $activity->preventseek ? max(5.0, (float)$progress->maxwatched + 8.0) : INF;
        $sql = "SELECT p.id, p.timeposition
                  FROM {videopredict_points} p
             LEFT JOIN {videopredict_responses} r ON r.pointid = p.id AND r.userid = :userid
                 WHERE p.videopredictid = :activityid
                   AND p.required = 1
                   AND r.id IS NULL
              ORDER BY p.timeposition ASC";
        $pending = $DB->get_record_sql($sql, ['userid' => $userid, 'activityid' => $activity->id], IGNORE_MULTIPLE);
        if ($pending) {
            $limit = min($limit, max(0, (float)$pending->timeposition + 0.5));
        }
        return is_infinite($limit) ? 1000000000.0 : $limit;
    }

    /**
     * Determine custom completion.
     */
    public function is_complete(\stdClass $activity, int $userid, ?\stdClass $progress = null): bool {
        global $DB;
        $progress = $progress ?: $this->get_progress($activity->id, $userid);
        if ((int)$activity->completionpercent > 0 && (float)$progress->percent + 0.001 < (int)$activity->completionpercent) {
            return false;
        }
        if (!empty($activity->completionpredictions)) {
            $required = $DB->count_records('videopredict_points', ['videopredictid' => $activity->id, 'required' => 1]);
            if ($required) {
                $sql = "SELECT COUNT(1)
                          FROM {videopredict_responses} r
                          JOIN {videopredict_points} p ON p.id = r.pointid
                         WHERE p.videopredictid = :activityid
                           AND p.required = 1
                           AND r.userid = :userid";
                if ((int)$DB->count_records_sql($sql, ['activityid' => $activity->id, 'userid' => $userid]) < $required) {
                    return false;
                }
            }
        }
        return !empty($activity->completionpredictions) || (int)$activity->completionpercent > 0;
    }

    /**
     * Calculate user grade on 0..100 scale.
     */
    public function calculate_grade(\stdClass $activity, int $userid): ?float {
        global $DB;
        $progress = $this->get_progress($activity->id, $userid);
        $progressgrade = min(100, max(0, (float)$progress->percent));
        $points = $DB->get_records('videopredict_points', ['videopredictid' => $activity->id]);
        $possible = 0.0;
        $awarded = 0.0;
        $hasresponse = false;
        foreach ($points as $point) {
            if ((float)$point->points <= 0) {
                continue;
            }
            $possible += (float)$point->points;
            $response = $DB->get_record('videopredict_responses', ['pointid' => $point->id, 'userid' => $userid]);
            if ($response) {
                $hasresponse = true;
                $awarded += min((float)$point->points, max(0, (float)$response->awarded));
            }
        }
        $predictiongrade = $possible > 0 ? ($awarded / $possible) * 100 : 0;
        if (!$hasresponse && $progressgrade <= 0) {
            return null;
        }
        if ($activity->grademode === 'progress') {
            return $progressgrade;
        }
        if ($activity->grademode === 'blended') {
            return ($predictiongrade + $progressgrade) / 2;
        }
        return $predictiongrade;
    }

    /**
     * Sync completion and gradebook.
     */
    public function sync_user(\stdClass $activity, int $userid): void {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');
        $progress = $this->get_progress($activity->id, $userid);
        $complete = $this->is_complete($activity, $userid, $progress);
        if ((int)$progress->completed !== (int)$complete) {
            $progress->completed = (int)$complete;
            $progress->timemodified = time();
            if ($progress->id) {
                $DB->update_record('videopredict_progress', $progress);
            }
        }
        if ($cm = get_coursemodule_from_instance('videopredict', $activity->id, $activity->course, false, IGNORE_MISSING)) {
            $completion = new \completion_info(get_course($activity->course));
            if ($completion->is_enabled($cm)) {
                $completion->update_state($cm, $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE, $userid);
            }
        }
        videopredict_update_grades($activity, $userid, true);
    }
}
