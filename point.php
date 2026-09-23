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
 * point.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Edit prediction point.
 */
require_once(__DIR__ . '/../../config.php');
$id = required_param('id', PARAM_INT);
$pointid = optional_param('pointid', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videopredict', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videopredict:managepoints', $context);
$PAGE->set_url('/mod/videopredict/point.php', ['id' => $cm->id, 'pointid' => $pointid]);
$PAGE->set_title(get_string('predictionpoint', 'videopredict'));
$PAGE->set_heading(format_string($course->fullname));
$manager = new \mod_videopredict\prediction_manager();
$point = $pointid ? $manager->get_point($pointid, $activity->id) : null;
$form = new \mod_videopredict\form\point_form(null, ['context' => $context]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videopredict/points.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    $parse = static function (string $value): float {
        $value = trim($value);
        if (is_numeric($value)) {
            return (float)$value;
        }
        $parts = array_map('floatval', explode(':', $value));
        $seconds = array_pop($parts) ?: 0;
        $minutes = array_pop($parts) ?: 0;
        $hours = array_pop($parts) ?: 0;
        return $hours * 3600 + $minutes * 60 + $seconds;
    };
    $now = time();
    $record = (object)[
        'id' => $pointid ?: null,
        'videopredictid' => $activity->id,
        'timeposition' => $parse($data->timepositiontext),
        'revealposition' => trim($data->revealpositiontext) === '' ?
            $parse($data->timepositiontext) + 10 :
            $parse($data->revealpositiontext),
        'title' => $data->title,
        'question' => $data->question_editor['text'],
        'questionformat' => $data->question_editor['format'],
        'responsetype' => $data->responsetype,
        'optionsjson' => $data->responsetype === 'truefalse' ? json_encode(
            [
                'true' => get_string('true', 'videopredict'),
                'false' => get_string('false', 'videopredict'),
            ]) : $manager->options_to_json((string)$data->optionstext),
        'correctanswer' => trim((string)$data->correctanswer),
        'resulttext' => $data->result_editor['text'],
        'resultformat' => $data->result_editor['format'],
        'reflectionquestion' => $data->reflection_editor['text'],
        'reflectionformat' => $data->reflection_editor['format'],
        'required' => !empty($data->required) ? 1 : 0,
        'pauseonreveal' => !empty($data->pauseonreveal) ? 1 : 0,
        'points' => max(0, (float)$data->points),
        'sortorder' => (int)$data->sortorder,
        'timemodified' => $now,
    ];
    if ($pointid) {
        $DB->update_record('videopredict_points', $record);
    } else {
        unset($record->id);
        $record->timecreated = $now;
        $DB->insert_record('videopredict_points', $record);
    }
    redirect(new moodle_url('/mod/videopredict/points.php', ['id' => $cm->id]), get_string('pointsaved', 'videopredict'));
}
if ($point) {
    $form->set_data((object)[
        'cmid' => $cm->id, 'pointid' => $point->id, 'title' => $point->title,
        'timepositiontext' => gmdate($point->timeposition >= 3600 ? 'H:i:s' : 'i:s', (int)$point->timeposition),
        'question_editor' => ['text' => $point->question, 'format' => $point->questionformat],
        'responsetype' => $point->responsetype, 'optionstext' => $manager->options_to_text($point->optionsjson),
        'correctanswer' => $point->correctanswer, 'required' => $point->required, 'points' => $point->points,
        'revealpositiontext' => gmdate($point->revealposition >= 3600 ? 'H:i:s' : 'i:s', (int)$point->revealposition),
        'result_editor' => ['text' => $point->resulttext, 'format' => $point->resultformat],
        'reflection_editor' => ['text' => $point->reflectionquestion, 'format' => $point->reflectionformat],
        'pauseonreveal' => $point->pauseonreveal, 'sortorder' => $point->sortorder,
    ]);
} else {
    $form->set_data((object)['cmid' => $cm->id, 'pointid' => 0]);
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
