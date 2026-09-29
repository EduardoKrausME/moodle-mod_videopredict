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
 * Backup and restore tests.
 *
 * @package   mod_videopredict
 * @category  test
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict;

use backup;
use backup_controller;
use backup_setting;
use restore_controller;
use restore_dbops;

/**
 * Backup and restore tests.
 */
final class restore_test extends \advanced_testcase {
    /**
     * User responses and progress survive a backup with user data.
     *
     * @coversNothing
     */
    public function test_backup_restore_with_user_data(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $activity = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);

        $pointid = $DB->insert_record('videopredict_points', (object)[
            'videopredictid' => $activity->id,
            'timeposition' => 0,
            'revealposition' => 10,
            'title' => 'Backup prediction',
            'question' => 'Question',
            'questionformat' => FORMAT_PLAIN,
            'responsetype' => 'multichoice',
            'optionsjson' => json_encode(['A' => 'A', 'B' => 'B']),
            'correctanswer' => 'A',
            'resulttext' => 'Result',
            'resultformat' => FORMAT_PLAIN,
            'reflectionquestion' => '',
            'reflectionformat' => FORMAT_PLAIN,
            'required' => 1,
            'pauseonreveal' => 0,
            'points' => 1,
            'sortorder' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $DB->insert_record('videopredict_responses', (object)[
            'pointid' => $pointid,
            'userid' => $student->id,
            'response' => 'A',
            'responsejson' => null,
            'iscorrect' => 1,
            'awarded' => 1,
            'reflection' => null,
            'understandingchanged' => -1,
            'predictiontime' => time(),
            'reflectiontime' => 0,
            'gradedby' => 0,
            'timemodified' => time(),
        ]);
        $DB->insert_record('videopredict_progress', (object)[
            'videopredictid' => $activity->id,
            'userid' => $student->id,
            'duration' => 100,
            'lastposition' => 20,
            'maxwatched' => 20,
            'uniquewatched' => 20,
            'totalwatchtime' => 20,
            'percent' => 20,
            'segments' => json_encode([[0, 20]]),
            'completed' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $newcourseid = $this->backup_and_restore($course);
        $restored = $DB->get_record('videopredict', ['course' => $newcourseid], '*', MUST_EXIST);
        $restoredpoint = $DB->get_record('videopredict_points', ['videopredictid' => $restored->id], '*', MUST_EXIST);
        $response = $DB->get_record('videopredict_responses', ['pointid' => $restoredpoint->id], '*', MUST_EXIST);
        $progress = $DB->get_record('videopredict_progress', ['videopredictid' => $restored->id], '*', MUST_EXIST);

        $this->assertSame('A', $response->response);
        $this->assertEquals(1, $response->iscorrect);
        $this->assertEquals(20.0, (float)$progress->percent);
        $this->assertGreaterThan(0, $response->userid);
        $this->assertEquals($response->userid, $progress->userid);
    }

    /**
     * Back up and restore a course with user data enabled.
     *
     * @param \stdClass $course Source course.
     * @return int Restored course id.
     */
    private function backup_and_restore(\stdClass $course): int {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value(true);
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        $newcourseid = restore_dbops::create_new_course(
            $course->fullname,
            $course->shortname . '_restored',
            $course->category
        );
        $rc = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_status(backup_setting::NOT_LOCKED);
        $rc->get_plan()->get_setting('users')->set_value(true);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}
