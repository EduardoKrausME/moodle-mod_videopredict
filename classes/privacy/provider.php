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
 * provider.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videopredict_progress', [
            'userid' => 'privacy:metadata:progress:userid',
            'duration' => 'privacy:metadata:progress:duration',
            'lastposition' => 'privacy:metadata:progress:lastposition',
            'maxwatched' => 'privacy:metadata:progress:maxwatched',
            'uniquewatched' => 'privacy:metadata:progress:uniquewatched',
            'totalwatchtime' => 'privacy:metadata:progress:totalwatchtime',
            'percent' => 'privacy:metadata:progress:percent',
            'segments' => 'privacy:metadata:progress:segments',
            'completed' => 'privacy:metadata:progress:completed',
            'timemodified' => 'privacy:metadata:progress:timemodified',
        ], 'privacy:metadata:progress');
        $collection->add_database_table('videopredict_responses', [
            'userid' => 'privacy:metadata:responses:userid',
            'response' => 'privacy:metadata:responses:response',
            'iscorrect' => 'privacy:metadata:responses:iscorrect',
            'awarded' => 'privacy:metadata:responses:awarded',
            'reflection' => 'privacy:metadata:responses:reflection',
            'understandingchanged' => 'privacy:metadata:responses:understandingchanged',
            'predictiontime' => 'privacy:metadata:responses:predictiontime',
            'reflectiontime' => 'privacy:metadata:responses:reflectiontime',
        ], 'privacy:metadata:responses');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {videopredict} v ON v.id = cm.instance
             LEFT JOIN {videopredict_progress} pr ON pr.videopredictid = v.id AND pr.userid = :userid1
             LEFT JOIN {videopredict_points} p ON p.videopredictid = v.id
             LEFT JOIN {videopredict_responses} r ON r.pointid = p.id AND r.userid = :userid2
                 WHERE pr.id IS NOT NULL OR r.id IS NOT NULL";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'videopredict',
            'userid1' => $userid,
            'userid2' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$cm = get_coursemodule_from_id('videopredict', $context->instanceid, 0, false, IGNORE_MISSING)) {
                continue;
            }
            $progress = $DB->get_record('videopredict_progress', ['videopredictid' => $cm->instance, 'userid' => $userid]);
            if ($progress) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:progresspath', 'videopredict')],
                    (object)[
                        'duration' => $progress->duration,
                        'lastposition' => $progress->lastposition,
                        'maxwatched' => $progress->maxwatched,
                        'uniquewatched' => $progress->uniquewatched,
                        'totalwatchtime' => $progress->totalwatchtime,
                        'percent' => $progress->percent,
                        'segments' => $progress->segments,
                        'completed' => transform::yesno($progress->completed),
                        'timemodified' => transform::datetime($progress->timemodified),
                    ]
                );
            }
            $sql = "SELECT r.*, p.title
                      FROM {videopredict_responses} r
                      JOIN {videopredict_points} p ON p.id = r.pointid
                     WHERE p.videopredictid = :activityid AND r.userid = :userid
                  ORDER BY p.timeposition";
            $responses = $DB->get_records_sql($sql, ['activityid' => $cm->instance, 'userid' => $userid]);
            foreach ($responses as $response) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:responsespath', 'videopredict'), format_string($response->title)],
                    (object)[
                        'response' => $response->response,
                        'iscorrect' => $response->iscorrect,
                        'awarded' => $response->awarded,
                        'reflection' => $response->reflection,
                        'understandingchanged' => $response->understandingchanged,
                        'predictiontime' => transform::datetime($response->predictiontime),
                        'reflectiontime' => $response->reflectiontime ? transform::datetime($response->reflectiontime) : '',
                    ]
                );
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id('videopredict', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }
        $pointids = $DB->get_fieldset_select('videopredict_points', 'id', 'videopredictid = :id', ['id' => $cm->instance]);
        if ($pointids) {
            [$insql, $params] = $DB->get_in_or_equal($pointids, SQL_PARAMS_NAMED, 'point');
            $DB->delete_records_select('videopredict_responses', "pointid {$insql}", $params);
        }
        $DB->delete_records('videopredict_progress', ['videopredictid' => $cm->instance]);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$cm = get_coursemodule_from_id('videopredict', $context->instanceid, 0, false, IGNORE_MISSING)) {
                continue;
            }
            $pointids = $DB->get_fieldset_select('videopredict_points', 'id', 'videopredictid = :id', ['id' => $cm->instance]);
            if ($pointids) {
                [$insql, $params] = $DB->get_in_or_equal($pointids, SQL_PARAMS_NAMED, 'point');
                $params['userid'] = $userid;
                $DB->delete_records_select('videopredict_responses', "userid = :userid AND pointid {$insql}", $params);
            }
            $DB->delete_records('videopredict_progress', ['videopredictid' => $cm->instance, 'userid' => $userid]);
        }
    }
}
