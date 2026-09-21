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
 * get_state.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_videopredict\prediction_manager;

/**
 * Get current student prediction state.
 */
class get_state extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(['cmid' => new external_value(PARAM_INT, 'Course module id')]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @return array Return value.
     */
    public static function execute(int $cmid): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid'));
        $cm = get_coursemodule_from_id('videopredict', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videopredict:view', $context);
        $activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
        $state = (new prediction_manager())->student_state($activity, $USER->id);
        foreach ($state['points'] as &$point) {
            $point['choicesjson'] = json_encode($point['choices'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            unset($point['choices']);
        }
        return $state;
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'points' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Point id'),
                'timeposition' => new external_value(PARAM_FLOAT, 'Prediction position'),
                'revealposition' => new external_value(PARAM_FLOAT, 'Reveal position'),
                'title' => new external_value(PARAM_TEXT, 'Title'),
                'question' => new external_value(PARAM_RAW, 'Question HTML'),
                'responsetype' => new external_value(PARAM_ALPHANUMEXT, 'Response type'),
                'choicesjson' => new external_value(PARAM_RAW, 'Choices JSON'),
                'required' => new external_value(PARAM_BOOL, 'Mandatory point'),
                'answered' => new external_value(PARAM_BOOL, 'Whether answered'),
                'response' => new external_value(PARAM_RAW, 'Original response'),
                'revealed' => new external_value(PARAM_BOOL, 'Whether result is visible'),
                'resulttext' => new external_value(PARAM_RAW, 'Result HTML'),
                'reflectionquestion' => new external_value(PARAM_RAW, 'Reflection question HTML'),
                'reflected' => new external_value(PARAM_BOOL, 'Whether reflection is saved'),
                'reflection' => new external_value(PARAM_RAW, 'Reflection text'),
                'understandingchanged' => new external_value(PARAM_INT, 'Understanding changed state'),
                'iscorrect' => new external_value(PARAM_INT, 'Correctness'),
                'pauseonreveal' => new external_value(PARAM_BOOL, 'Pause at reveal'),
            ])),
            'progress' => new external_single_structure([
                'percent' => new external_value(PARAM_FLOAT, 'Watched percentage'),
                'lastposition' => new external_value(PARAM_FLOAT, 'Last position'),
                'maxwatched' => new external_value(PARAM_FLOAT, 'Maximum reached position'),
                'duration' => new external_value(PARAM_FLOAT, 'Duration'),
                'maxposition' => new external_value(PARAM_FLOAT, 'Maximum allowed seek position'),
                'completed' => new external_value(PARAM_BOOL, 'Completion state'),
            ]),
        ]);
    }
}
