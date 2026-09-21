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
 * custom_completion.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\completion;

use core_completion\activity_custom_completion;

/**
 * Custom completion rules.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Completion state for one rule.
     */
    public function get_state(string $rule): int {
        global $DB;
        $activity = $DB->get_record('videopredict', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $manager = new \mod_videopredict\progress_manager();
        if ($rule === 'completionpredictions') {
            if (empty($activity->completionpredictions)) {
                return COMPLETION_INCOMPLETE;
            }
            $required = $DB->count_records('videopredict_points', ['videopredictid' => $activity->id, 'required' => 1]);
            if (!$required) {
                return COMPLETION_COMPLETE;
            }
            $sql = "SELECT COUNT(1)
                      FROM {videopredict_responses} r
                      JOIN {videopredict_points} p ON p.id = r.pointid
                     WHERE p.videopredictid = :activityid
                       AND p.required = 1
                       AND r.userid = :userid";
            $done = $DB->count_records_sql($sql, ['activityid' => $activity->id, 'userid' => $this->userid]);
            return $done >= $required ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        if ($rule === 'completionpercent') {
            if ((int)$activity->completionpercent <= 0) {
                return COMPLETION_INCOMPLETE;
            }
            $progress = $manager->get_progress($activity->id, $this->userid);
            return (float)$progress->percent + 0.001 >= (int)$activity->completionpercent
                ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        return COMPLETION_INCOMPLETE;
    }

    /**
     * Active rules.
     */
    public static function get_defined_custom_rules(): array {
        return ['completionpredictions', 'completionpercent'];
    }

    /**
     * Descriptions.
     */
    public function get_custom_rule_descriptions(): array {
        $descriptions = [];
        if (!empty($this->cm->customdata['customcompletionrules']['completionpredictions'])) {
            $descriptions['completionpredictions'] = get_string('completiondetail:predictions', 'videopredict');
        }
        $percent = (int)($this->cm->customdata['customcompletionrules']['completionpercent'] ?? 0);
        if ($percent > 0) {
            $descriptions['completionpercent'] = get_string('completiondetail:percent', 'videopredict', $percent);
        }
        return $descriptions;
    }

    /**
     * Sort order.
     */
    public function get_sort_order(): array {
        return ['completionpredictions', 'completionpercent'];
    }
}
