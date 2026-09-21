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
 * Student view.
 *
 * @package mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videopredict', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videopredict:view', $context);

$PAGE->set_url('/mod/videopredict/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$event = \mod_videopredict\event\course_module_viewed::create([
    'objectid' => $activity->id,
    'context' => $context,
]);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('videopredict', $activity);
$event->trigger();
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$manager = new \mod_videopredict\prediction_manager();
$state = $manager->student_state($activity, $USER->id);

$videourl = '';
if ($activity->videosource === 'upload') {
    $files = get_file_storage()->get_area_files(
        $context->id, 'mod_videopredict', 'video', 0, 'filename', false);
    if ($files) {
        $file = reset($files);
        $videourl = moodle_url::make_pluginfile_url(
            $context->id, 'mod_videopredict', 'video', 0, '/', $file->get_filename())->out(false);
    }
} else {
    $videourl = (string)$activity->videourl;
}
$poster = (string)$activity->posterurl;
$posterfiles = get_file_storage()->get_area_files($context->id, 'mod_videopredict', 'poster', 0, 'filename', false);
if ($posterfiles) {
    $file = reset($posterfiles);
    $poster = moodle_url::make_pluginfile_url(
        $context->id, 'mod_videopredict', 'poster', 0, '/', $file->get_filename())->out(false);
}

$points = [];
foreach ($state['points'] as $point) {
    $point['timeformatted'] = gmdate(((int)$point['timeposition'] >= 3600 ? 'H:i:s' : 'i:s'), (int)$point['timeposition']);
    $points[] = $point;
}

$templatedata = [
    'cmid' => $cm->id,
    'name' => format_string($activity->name),
    'intro' => format_module_intro('videopredict', $activity, $cm->id, false),
    'videosource' => $activity->videosource,
    'videourl' => $videourl,
    'poster' => $poster,
    'points' => $points,
    'percent' => round((float)$state['progress']['percent'], 1),
    'manageurl' => has_capability('mod/videopredict:managepoints', $context) ?
        (new moodle_url('/mod/videopredict/points.php', ['id' => $cm->id]))->out(false) : '',
    'reporturl' => has_capability('mod/videopredict:viewreport', $context) ?
        (new moodle_url('/mod/videopredict/report.php', ['id' => $cm->id]))->out(false) : '',
];

$config = [
    'cmid' => $cm->id,
    'source' => $activity->videosource,
    'url' => $videourl,
    'preventseek' => (bool)$activity->preventseek,
    'resumeplayback' => (bool)$activity->resumeplayback,
    'lastposition' => (float)$state['progress']['lastposition'],
    'maxposition' => (float)$state['progress']['maxposition'],
    'strings' => [
        'submit' => get_string('submitprediction', 'videopredict'),
        'continue' => get_string('continuevideo', 'videopredict'),
        'changedyes' => get_string('understandingchangedyes', 'videopredict'),
        'changedno' => get_string('understandingchangedno', 'videopredict'),
        'reflection' => get_string('savereflection', 'videopredict'),
        'predictionlocked' => get_string('predictionlockednotice', 'videopredict'),
        'seekblocked' => get_string('seekblocked', 'videopredict'),
        'correct' => get_string('correct', 'videopredict'),
        'incorrect' => get_string('incorrect', 'videopredict'),
    ],
];
$PAGE->requires->js_call_amd('mod_videopredict/player', 'init', [$config]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videopredict/player', $templatedata);
echo $OUTPUT->footer();
