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
 * userreport.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Detailed user report and manual grading.
 */
require_once(__DIR__ . '/../../config.php');
$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$responseid = optional_param('responseid', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videopredict', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videopredict:viewreport', $context);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$PAGE->set_url('/mod/videopredict/userreport.php', ['id' => $cm->id, 'userid' => $userid]);
$PAGE->set_title(get_string('studentdetails', 'videopredict'));
$PAGE->set_heading(format_string($course->fullname));

if ($responseid && has_capability('mod/videopredict:grade', $context)) {
    $response = $DB->get_record('videopredict_responses', ['id' => $responseid, 'userid' => $userid], '*', MUST_EXIST);
    $point = $DB->get_record('videopredict_points',
        ['id' => $response->pointid, 'videopredictid' => $activity->id], '*', MUST_EXIST);
    $form = new \mod_videopredict\form\manual_grade_form();
    if ($form->is_cancelled()) {
        redirect(new moodle_url('/mod/videopredict/userreport.php', ['id' => $cm->id, 'userid' => $userid]));
    }
    if ($data = $form->get_data()) {
        $response->iscorrect = (int)$data->iscorrect;
        $response->awarded = max(0, min((float)$point->points, (float)$data->awarded));
        $response->gradedby = $USER->id;
        $response->timemodified = time();
        $DB->update_record('videopredict_responses', $response);
        (new \mod_videopredict\progress_manager())->sync_user($activity, $userid);
        redirect(new moodle_url('/mod/videopredict/userreport.php',
            ['id' => $cm->id, 'userid' => $userid]), get_string('gradesaved', 'videopredict'));
    }
    $form->set_data((object)[
        'id' => $cm->id,
        'responseid' => $response->id,
        'iscorrect' => $response->iscorrect,
        'awarded' => $response->awarded,
    ]);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('gradeprediction', 'videopredict'));
    echo html_writer::div(format_text($point->question, $point->questionformat), 'mb-3');
    echo html_writer::div(s($response->response), 'alert alert-secondary');
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

$progress = (new \mod_videopredict\progress_manager())->get_progress($activity->id, $userid);
$sql = "SELECT p.*, r.id AS responseid, r.response, r.iscorrect, r.awarded, r.reflection,
               r.understandingchanged, r.predictiontime, r.reflectiontime
          FROM {videopredict_points} p
     LEFT JOIN {videopredict_responses} r ON r.pointid = p.id AND r.userid = :userid
         WHERE p.videopredictid = :activityid
      ORDER BY p.timeposition, p.sortorder, p.id";
$records = $DB->get_records_sql($sql, ['userid' => $userid, 'activityid' => $activity->id]);
echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($user));
echo html_writer::tag('p', get_string('watchedpercentvalue', 'videopredict', round((float)$progress->percent, 1)));
$table = new html_table();
$table->head = [
    get_string('time'),
    get_string('predictionquestion', 'videopredict'),
    get_string('answer', 'videopredict'),
    get_string('correctness', 'videopredict'),
    get_string('reflection', 'videopredict'),
    get_string('understandingchanged', 'videopredict'),
    get_string('actions'),
];
foreach ($records as $record) {
    if (!$record->responseid) {
        $answer = '-';
        $correctness = '-';
        $reflection = '-';
        $changed = '-';
        $action = '';
    } else {
        $choices = (new \mod_videopredict\prediction_manager())->choices($record);
        $answer = $choices[$record->response] ?? $record->response;
        $correctness = (int)$record->iscorrect < 0 ?
            get_string('notgraded', 'videopredict') :
            ((int)$record->iscorrect ?
                get_string('correct', 'videopredict') :
                get_string('incorrect', 'videopredict'));
        $reflection = $record->reflection ?: '-';
        $changed = (int)$record->understandingchanged < 0 ? '-' :
            ((int)$record->understandingchanged ? get_string('yes') : get_string('no'));
        $action = has_capability('mod/videopredict:grade', $context) && $record->responsetype === 'open' ?
            html_writer::link(new moodle_url('/mod/videopredict/userreport.php',
                ['id' => $cm->id, 'userid' => $userid, 'responseid' => $record->responseid]), get_string('grade', 'grades')) : '';
    }
    $table->data[] = [
        gmdate($record->timeposition >= 3600 ? 'H:i:s' : 'i:s', (int)$record->timeposition),
        format_text($record->question, $record->questionformat),
        s($answer),
        $correctness,
        s($reflection),
        $changed,
        $action,
    ];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
