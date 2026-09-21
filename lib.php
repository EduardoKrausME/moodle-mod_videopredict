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
 * Core callbacks for Video Prediction.
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videopredict\progress_manager;

/**
 * Module feature support.
 */
function videopredict_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * Add an instance.
 */
function videopredict_add_instance(stdClass $data, ?mod_videopredict_mod_form $mform = null): int {
    global $DB;
    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    $data->grade = isset($data->grade) ? (float)$data->grade : 100;
    $id = $DB->insert_record('videopredict', $data);
    $data->id = $id;
    videopredict_save_files($data);
    videopredict_grade_item_update($data);
    return $id;
}

/**
 * Update an instance.
 */
function videopredict_update_instance(stdClass $data, ?mod_videopredict_mod_form $mform = null): bool {
    global $DB;
    $data->id = $data->instance;
    $data->timemodified = time();
    $result = $DB->update_record('videopredict', $data);
    videopredict_save_files($data);
    videopredict_grade_item_update($data);
    videopredict_update_grades($data);
    return $result;
}

/**
 * Delete an instance.
 */
function videopredict_delete_instance(int $id): bool {
    global $DB;
    if (!$activity = $DB->get_record('videopredict', ['id' => $id])) {
        return false;
    }
    $pointids = $DB->get_fieldset_select('videopredict_points', 'id', 'videopredictid = :id', ['id' => $id]);
    if ($pointids) {
        [$insql, $params] = $DB->get_in_or_equal($pointids, SQL_PARAMS_NAMED, 'point');
        $DB->delete_records_select('videopredict_responses', "pointid {$insql}", $params);
    }
    $DB->delete_records('videopredict_points', ['videopredictid' => $id]);
    $DB->delete_records('videopredict_progress', ['videopredictid' => $id]);
    $DB->delete_records('videopredict', ['id' => $id]);
    if ($cm = get_coursemodule_from_instance('videopredict', $id, $activity->course, false, IGNORE_MISSING)) {
        $context = context_module::instance($cm->id);
        get_file_storage()->delete_area_files($context->id, 'mod_videopredict');
    }
    videopredict_grade_item_delete($activity);
    return true;
}

/**
 * Save uploaded video/poster files.
 */
function videopredict_save_files(stdClass $activity): void {
    $cmid = !empty($activity->coursemodule) ? (int)$activity->coursemodule : 0;
    if (!$cmid) {
        $cm = get_coursemodule_from_instance('videopredict', $activity->id, $activity->course, false, IGNORE_MISSING);
        $cmid = $cm ? (int)$cm->id : 0;
    }
    if (!$cmid) {
        return;
    }
    $context = context_module::instance($cmid);
    if (isset($activity->videofile)) {
        file_save_draft_area_files((int)$activity->videofile, $context->id, 'mod_videopredict', 'video', 0,
            ['subdirs' => 0, 'maxfiles' => 1]);
    }
    if (isset($activity->poster)) {
        file_save_draft_area_files((int)$activity->poster, $context->id, 'mod_videopredict', 'poster', 0,
            ['subdirs' => 0, 'maxfiles' => 1]);
    }
}

/**
 * File serving callback.
 */
function mod_videopredict_pluginfile($course, $cm, $context, string $filearea, array $args,
                                     bool $forcedownload, array $options = []): bool {
    if ($context->contextlevel !== CONTEXT_MODULE || !in_array($filearea, ['video', 'poster'], true)) {
        return false;
    }
    require_login($course, true, $cm);
    require_capability('mod/videopredict:view', $context);
    $itemid = (int)array_shift($args);
    if ($itemid !== 0) {
        return false;
    }
    $filename = array_pop($args);
    $filepath = '/' . ($args ? implode('/', $args) . '/' : '');
    $file = get_file_storage()->get_file($context->id, 'mod_videopredict', $filearea, 0, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * File areas.
 */
function videopredict_get_file_areas($course, $cm, $context): array {
    return [
        'video' => get_string('videofile', 'videopredict'),
        'poster' => get_string('poster', 'videopredict'),
    ];
}

/**
 * Prepare draft file areas for editing.
 */
function videopredict_prepare_editing_data(stdClass $data, context_module $context): stdClass {
    $videoitemid = file_get_submitted_draft_itemid('videofile');
    file_prepare_draft_area($videoitemid, $context->id, 'mod_videopredict', 'video', 0,
        ['subdirs' => 0, 'maxfiles' => 1]);
    $data->videofile = $videoitemid;
    $posteritemid = file_get_submitted_draft_itemid('poster');
    file_prepare_draft_area($posteritemid, $context->id, 'mod_videopredict', 'poster', 0,
        ['subdirs' => 0, 'maxfiles' => 1]);
    $data->poster = $posteritemid;
    return $data;
}

/**
 * Grade item update.
 */
function videopredict_grade_item_update(stdClass $activity, array|null $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    $params = [
        'itemname' => clean_param($activity->name, PARAM_NOTAGS),
        'gradetype' => ((float)$activity->grade > 0) ? GRADE_TYPE_VALUE : GRADE_TYPE_NONE,
        'grademin' => 0,
        'grademax' => max(0, (float)$activity->grade),
    ];
    return grade_update('mod/videopredict', $activity->course, 'mod', 'videopredict', $activity->id, 0, $grades, $params);
}

/**
 * Publish calculated grades.
 */
function videopredict_update_grades(stdClass $activity, int $userid = 0, bool $nullifnone = true): void {
    global $DB;
    $manager = new progress_manager();
    $userids = [];
    if ($userid) {
        $userids[] = $userid;
    } else {
        $userids = $DB->get_fieldset_select('videopredict_progress', 'userid', 'videopredictid = :id', ['id' => $activity->id]);
        $sql = "SELECT DISTINCT r.userid
                  FROM {videopredict_responses} r
                  JOIN {videopredict_points} p ON p.id = r.pointid
                 WHERE p.videopredictid = :id";
        $userids = array_unique(array_merge($userids, $DB->get_fieldset_sql($sql, ['id' => $activity->id])));
    }
    $grades = [];
    foreach ($userids as $uid) {
        $score100 = $manager->calculate_grade($activity, (int)$uid);
        $raw = $score100 === null ? null : ($score100 / 100) * (float)$activity->grade;
        $grades[(int)$uid] = (object)['userid' => (int)$uid, 'rawgrade' => $raw];
    }
    if (!$grades && $userid && $nullifnone) {
        $grades[$userid] = (object)['userid' => $userid, 'rawgrade' => null];
    }
    videopredict_grade_item_update($activity, $grades);
}

/**
 * Delete grade item.
 */
function videopredict_grade_item_delete(stdClass $activity): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update('mod/videopredict', $activity->course, 'mod', 'videopredict', $activity->id, 0, null, ['deleted' => 1]);
}

/**
 * Course-module info.
 */
function videopredict_get_coursemodule_info(stdClass $cm): ?cached_cm_info {
    global $DB;
    $activity = $DB->get_record('videopredict', ['id' => $cm->instance],
        'id,name,intro,introformat,completionpredictions,completionpercent');
    if (!$activity) {
        return null;
    }
    $info = new cached_cm_info();
    $info->name = $activity->name;
    if ($cm->showdescription) {
        $info->content = format_module_intro('videopredict', $activity, $cm->id, false);
    }
    $info->customdata = ['customcompletionrules' => [
        'completionpredictions' => (bool)$activity->completionpredictions,
        'completionpercent' => (int)$activity->completionpercent,
    ]];
    return $info;
}

/**
 * Completion descriptions.
 */
function videopredict_get_completion_active_rule_descriptions(cached_cm_info $cm): array {
    if ((int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }
    $rules = $cm->customdata['customcompletionrules'] ?? [];
    $descriptions = [];
    if (!empty($rules['completionpredictions'])) {
        $descriptions[] = get_string('completiondetail:predictions', 'videopredict');
    }
    if (!empty($rules['completionpercent'])) {
        $descriptions[] = get_string('completiondetail:percent', 'videopredict', $rules['completionpercent']);
    }
    return $descriptions;
}

/**
 * Legacy completion callback.
 */
function videopredict_get_completion_state($course, $cm, int $userid, bool $type): bool {
    global $DB;
    $activity = $DB->get_record('videopredict', ['id' => $cm->instance], '*', MUST_EXIST);
    return (new progress_manager())->is_complete($activity, $userid);
}
