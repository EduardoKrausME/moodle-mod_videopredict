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
 * backup_videopredict_stepslib.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Backup structure.
 */
class backup_videopredict_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');
        $activity = new backup_nested_element('videopredict', ['id'], [
            'course', 'name', 'intro', 'introformat', 'videosource', 'videourl', 'posterurl', 'preventseek',
            'resumeplayback', 'completionpredictions', 'completionpercent', 'grademode', 'grade', 'timecreated', 'timemodified'
        ]);
        $points = new backup_nested_element('points');
        $point = new backup_nested_element('point', ['id'], [
            'timeposition', 'revealposition', 'title', 'question', 'questionformat', 'responsetype', 'optionsjson',
            'correctanswer', 'resulttext', 'resultformat', 'reflectionquestion', 'reflectionformat', 'required',
            'pauseonreveal', 'points', 'sortorder', 'timecreated', 'timemodified'
        ]);
        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', ['id'], [
            'userid', 'response', 'responsejson', 'iscorrect', 'awarded', 'reflection', 'understandingchanged',
            'predictiontime', 'reflectiontime', 'gradedby', 'timemodified'
        ]);
        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'duration', 'lastposition', 'maxwatched', 'uniquewatched', 'totalwatchtime', 'percent',
            'segments', 'completed', 'timecreated', 'timemodified'
        ]);
        $activity->add_child($points);
        $points->add_child($point);
        $point->add_child($responses);
        $responses->add_child($response);
        $activity->add_child($progresses);
        $progresses->add_child($progress);
        $activity->set_source_table('videopredict', ['id' => backup::VAR_ACTIVITYID]);
        $point->set_source_table('videopredict_points', ['videopredictid' => backup::VAR_PARENTID]);
        if ($userinfo) {
            $response->set_source_table('videopredict_responses', ['pointid' => backup::VAR_PARENTID]);
            $progress->set_source_table('videopredict_progress', ['videopredictid' => backup::VAR_ACTIVITYID]);
        }
        $response->annotate_ids('user', 'userid');
        $response->annotate_ids('user', 'gradedby');
        $progress->annotate_ids('user', 'userid');
        $activity->annotate_files('mod_videopredict', 'video', null);
        $activity->annotate_files('mod_videopredict', 'poster', null);
        return $this->prepare_activity_structure($activity);
    }
}
