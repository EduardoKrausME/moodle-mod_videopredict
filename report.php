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
 * Activity report.
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$page = max(0, optional_param('page', 0, PARAM_INT));
$perpage = 50;

$cm = get_coursemodule_from_id('videopredict', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videopredict:viewreport', $context);

$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = $groupmode === NOGROUPS ? 0 : groups_get_activity_group($cm, true);
$accessallgroups = has_capability('moodle/site:accessallgroups', $context);

$urlparams = ['id' => $cm->id];
if ($currentgroup) {
    $urlparams['group'] = $currentgroup;
}
$PAGE->set_url('/mod/videopredict/report.php', $urlparams);
$PAGE->set_title(get_string('reporttitle', 'videopredict'));
$PAGE->set_heading(format_string($course->fullname));

$users = [];
$totalusers = 0;
if (!($groupmode === SEPARATEGROUPS && !$accessallgroups && !$currentgroup)) {
    $totalusers = count_enrolled_users($context, 'mod/videopredict:view', $currentgroup);
    $limitfrom = $download === 'csv' ? 0 : $page * $perpage;
    $limitnum = $download === 'csv' ? 0 : $perpage;
    $users = get_enrolled_users(
        $context,
        'mod/videopredict:view',
        $currentgroup,
        'u.id,u.firstname,u.lastname,u.email',
        'u.lastname,u.firstname',
        $limitfrom,
        $limitnum
    );
}

$points = $DB->get_records(
    'videopredict_points',
    ['videopredictid' => $activity->id],
    'timeposition,sortorder,id'
);
$userids = array_map('intval', array_keys($users));
$progressbyuser = [];
$responsesbyuser = [];

if ($userids) {
    [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'reportuser');
    $userparams['activityid'] = $activity->id;
    $progressrecords = $DB->get_records_select(
        'videopredict_progress',
        "videopredictid = :activityid AND userid {$usersql}",
        $userparams
    );
    foreach ($progressrecords as $progress) {
        $progressbyuser[(int)$progress->userid] = $progress;
    }

    if ($points) {
        [$pointsql, $pointparams] = $DB->get_in_or_equal(array_keys($points), SQL_PARAMS_NAMED, 'reportpoint');
        [$responsesusersql, $responsesuserparams] = $DB->get_in_or_equal(
            $userids,
            SQL_PARAMS_NAMED,
            'responseuser'
        );
        $responses = $DB->get_records_select(
            'videopredict_responses',
            "pointid {$pointsql} AND userid {$responsesusersql}",
            array_merge($pointparams, $responsesuserparams)
        );
        foreach ($responses as $response) {
            $responsesbyuser[(int)$response->userid][(int)$response->pointid] = $response;
        }
    }
}

$rows = [];
$pm = new \mod_videopredict\progress_manager();
foreach ($users as $user) {
    $userid = (int)$user->id;
    $progress = $progressbyuser[$userid] ?? (object)['percent' => 0];
    $responses = $responsesbyuser[$userid] ?? [];

    $predictioncount = count($responses);
    $correct = 0;
    $graded = 0;
    $changed = 0;
    foreach ($responses as $response) {
        if ((int)$response->iscorrect >= 0) {
            $graded++;
            if ((int)$response->iscorrect === 1) {
                $correct++;
            }
        }
        if ((int)$response->understandingchanged === 1) {
            $changed++;
        }
    }

    $grade = $pm->calculate_grade_from_data($activity, $progress, $points, $responses);
    $rows[] = (object)[
        'userid' => $userid,
        'fullname' => fullname($user),
        'email' => $user->email,
        'predictions' => $predictioncount,
        'correct' => $correct,
        'graded' => $graded,
        'changed' => $changed,
        'percent' => round((float)$progress->percent, 1),
        'grade' => $grade === null ? '' : round($grade, 1),
    ];
}

if ($download === 'csv') {
    require_capability('mod/videopredict:exportreport', $context);
    require_once($CFG->libdir . '/csvlib.class.php');

    $csv = new csv_export_writer();
    $csv->set_filename('videopredict-' . clean_filename($activity->name));
    $csv->add_data([
        get_string('student', 'videopredict'),
        get_string('email'),
        get_string('predictions', 'videopredict'),
        get_string('correctanswers', 'videopredict'),
        get_string('understandingchanges', 'videopredict'),
        get_string('watchedpercent', 'videopredict'),
        get_string('grade'),
    ]);
    foreach ($rows as $row) {
        $csv->add_data([
            $row->fullname,
            $row->email,
            $row->predictions,
            $row->graded ? $row->correct . '/' . $row->graded : '',
            $row->changed,
            $row->percent,
            $row->grade,
        ]);
    }
    $csv->download_file();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reporttitle', 'videopredict'));

if ($groupmode !== NOGROUPS) {
    groups_print_activity_menu($cm, $PAGE->url);
}
if (has_capability('mod/videopredict:exportreport', $context)) {
    $exportparams = ['id' => $cm->id, 'download' => 'csv'];
    if ($currentgroup) {
        $exportparams['group'] = $currentgroup;
    }
    echo $OUTPUT->single_button(
        new moodle_url('/mod/videopredict/report.php', $exportparams),
        get_string('exportcsv', 'videopredict'),
        'get'
    );
}

if ($totalusers > $perpage) {
    echo $OUTPUT->paging_bar($totalusers, $page, $perpage, $PAGE->url);
}

$table = new html_table();
$table->head = [
    get_string('student', 'videopredict'),
    get_string('predictions', 'videopredict'),
    get_string('correctanswers', 'videopredict'),
    get_string('understandingchanges', 'videopredict'),
    get_string('watchedpercent', 'videopredict'),
    get_string('grade'),
    get_string('actions'),
];
foreach ($rows as $row) {
    $detail = html_writer::link(
        new moodle_url('/mod/videopredict/userreport.php', ['id' => $cm->id, 'userid' => $row->userid]),
        get_string('viewdetails', 'videopredict')
    );
    $table->data[] = [
        $row->fullname,
        $row->predictions,
        $row->graded ? $row->correct . '/' . $row->graded : '-',
        $row->changed,
        $row->percent . '%',
        $row->grade,
        $detail,
    ];
}
echo html_writer::table($table);

if ($totalusers > $perpage) {
    echo $OUTPUT->paging_bar($totalusers, $page, $perpage, $PAGE->url);
}

echo $OUTPUT->footer();
