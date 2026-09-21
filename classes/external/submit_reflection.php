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
 * submit_reflection.php
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
use mod_videopredict\prediction_manager;

/**
 * Submit post-result reflection.
 */
class submit_reflection extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'pointid' => new external_value(PARAM_INT, 'Prediction point id'),
            'reflection' => new external_value(PARAM_RAW, 'Reflection text'),
            'changed' => new external_value(PARAM_INT, 'Whether understanding changed'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param int $pointid Parameter pointid.
     * @param string $reflection Parameter reflection.
     * @param int $changed Parameter changed.
     * @return array Return value.
     */
    public static function execute(int $cmid, int $pointid, string $reflection, int $changed): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'pointid', 'reflection', 'changed'));
        $cm = get_coursemodule_from_id('videopredict', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videopredict:view', $context);
        $activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
        $manager = new prediction_manager();
        $point = $manager->get_point($params['pointid'], $activity->id);
        $record = $manager->submit_reflection($activity, $point, $USER->id, $params['reflection'], $params['changed']);
        return ['saved' => true, 'reflectiontime' => (int)$record->reflectiontime];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'saved' => new external_value(PARAM_BOOL, 'Whether reflection was saved'),
            'reflectiontime' => new external_value(PARAM_INT, 'Saved timestamp'),
        ]);
    }
}
