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
 * points.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Manage prediction points.
 */
require_once(__DIR__ . '/../../config.php');
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videopredict', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videopredict:managepoints', $context);
if ($delete && confirm_sesskey()) {
    $DB->delete_records('videopredict_responses', ['pointid' => $delete]);
    $DB->delete_records('videopredict_points', ['id' => $delete, 'videopredictid' => $activity->id]);
    redirect(new moodle_url('/mod/videopredict/points.php', ['id' => $cm->id]), get_string('pointdeleted', 'videopredict'));
}
$PAGE->set_url('/mod/videopredict/points.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('managepoints', 'videopredict'));
$PAGE->set_heading(format_string($course->fullname));
$points = $DB->get_records('videopredict_points', ['videopredictid' => $activity->id], 'timeposition, sortorder, id');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managepoints', 'videopredict'));
echo $OUTPUT->single_button(new moodle_url('/mod/videopredict/point.php',
    ['id' => $cm->id]), get_string('addpredictionpoint', 'videopredict'), 'get');
if (!$points) {
    echo $OUTPUT->notification(get_string('nopoints', 'videopredict'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('time'),
        get_string('pointtitle', 'videopredict'),
        get_string('responsetype', 'videopredict'),
        get_string('required'),
        get_string('actions'),
    ];
    foreach ($points as $point) {
        $edit = html_writer::link(new moodle_url('/mod/videopredict/point.php',
            ['id' => $cm->id, 'pointid' => $point->id]), get_string('edit'));
        $del = html_writer::link(new moodle_url('/mod/videopredict/points.php',
            ['id' => $cm->id, 'delete' => $point->id, 'sesskey' => sesskey()]), get_string('delete'));
        $table->data[] = [
            gmdate($point->timeposition >= 3600 ? 'H:i:s' : 'i:s', (int)$point->timeposition),
            format_string($point->title), get_string('type:' . $point->responsetype, 'videopredict'),
            $point->required ? get_string('yes') : get_string('no'), $edit . ' · ' . $del,
        ];
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
