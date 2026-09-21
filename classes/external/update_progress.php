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

/**
 * update_progress.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use mod_videopredict\progress_manager;

/**
 * Update watched progress.
 */
class update_progress extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'start' => new external_value(PARAM_FLOAT, 'Played segment start'),
            'end' => new external_value(PARAM_FLOAT, 'Played segment end'),
            'position' => new external_value(PARAM_FLOAT, 'Current position'),
            'duration' => new external_value(PARAM_FLOAT, 'Video duration'),
            'elapsed' => new external_value(PARAM_FLOAT, 'Elapsed real playback time'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param float $start Parameter start.
     * @param float $end Parameter end.
     * @param float $position Parameter position.
     * @param float $duration Parameter duration.
     * @param float $elapsed Parameter elapsed.
     * @return array Return value.
     */
    public static function execute(int $cmid, float $start, float $end, float $position, float $duration, float $elapsed): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(),
            compact('cmid', 'start', 'end', 'position', 'duration', 'elapsed'));
        $cm = get_coursemodule_from_id('videopredict', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videopredict:view', $context);
        $activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
        $manager = new progress_manager();
        $progress = $manager->get_progress($activity->id, $USER->id);
        $maxallowed = $manager->max_allowed_position($activity, $USER->id, $progress);
        $position = min(max(0, $params['position']), $maxallowed);
        $start = min(max(0, $params['start']), $maxallowed);
        $end = min(max($start, $params['end']), $maxallowed);
        $elapsed = max(0, min(30, $params['elapsed']));
        if ($elapsed + 2.5 < ($end - $start)) {
            $end = $start + $elapsed + 2.5;
        }
        $segments = json_decode((string)$progress->segments, true);
        $segments = is_array($segments) ? $segments : [];
        if ($end > $start && $elapsed > 0) {
            $segments = $manager->merge_segments($segments, $start, $end);
        }
        $duration = max((float)$progress->duration, min(86400, max(0, $params['duration'])));
        $unique = $manager->unique_seconds($segments);
        $percent = $duration > 0 ? min(100, ($unique / $duration) * 100) : 0;
        $progress->duration = $duration;
        $progress->lastposition = $position;
        $progress->maxwatched = max((float)$progress->maxwatched, $end, $position);
        $progress->uniquewatched = $unique;
        $progress->totalwatchtime = (float)$progress->totalwatchtime + $elapsed;
        $progress->percent = $percent;
        $progress->segments = json_encode($segments, JSON_UNESCAPED_SLASHES);
        $progress->timemodified = time();
        if ($progress->id) {
            $DB->update_record('videopredict_progress', $progress);
        } else {
            $progress->id = $DB->insert_record('videopredict_progress', $progress);
        }
        $manager->sync_user($activity, $USER->id);
        $progress = $manager->get_progress($activity->id, $USER->id);
        return [
            'percent' => (float)$progress->percent,
            'position' => (float)$progress->lastposition,
            'maxwatched' => (float)$progress->maxwatched,
            'maxposition' => $manager->max_allowed_position($activity, $USER->id, $progress),
            'completed' => (bool)$progress->completed,
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'percent' => new external_value(PARAM_FLOAT, 'Watched percentage'),
            'position' => new external_value(PARAM_FLOAT, 'Approved current position'),
            'maxwatched' => new external_value(PARAM_FLOAT, 'Maximum reached position'),
            'maxposition' => new external_value(PARAM_FLOAT, 'Maximum allowed seek position'),
            'completed' => new external_value(PARAM_BOOL, 'Completion state'),
        ]);
    }
}
