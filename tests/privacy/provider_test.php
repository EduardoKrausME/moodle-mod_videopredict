<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Privacy provider tests.
 *
 * @package   mod_videopredict
 * @category  test
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\privacy;

use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\tests\provider_testcase;

/**
 * Privacy provider tests.
 */
final class provider_test extends provider_testcase {
    /**
     * Graders are included in Privacy API discovery and deletion anonymises gradedby.
     */
    public function test_grader_is_discovered_and_anonymised(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('videopredict', $activity->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $pointid = $DB->insert_record('videopredict_points', (object)[
            'videopredictid' => $activity->id,
            'timeposition' => 0,
            'revealposition' => 10,
            'title' => 'Prediction',
            'question' => 'Question',
            'questionformat' => FORMAT_PLAIN,
            'responsetype' => 'open',
            'optionsjson' => null,
            'correctanswer' => '',
            'resulttext' => '',
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
        $responseid = $DB->insert_record('videopredict_responses', (object)[
            'pointid' => $pointid,
            'userid' => $student->id,
            'response' => 'Answer',
            'responsejson' => null,
            'iscorrect' => 1,
            'awarded' => 1,
            'reflection' => null,
            'understandingchanged' => -1,
            'predictiontime' => time(),
            'reflectiontime' => 0,
            'gradedby' => $teacher->id,
            'timemodified' => time(),
        ]);

        $contexts = provider::get_contexts_for_userid($teacher->id);
        $this->assertContainsEquals($context->id, $contexts->get_contextids());

        $users = new userlist($context, 'mod_videopredict');
        provider::get_users_in_context($users);
        $this->assertContains($student->id, $users->get_userids());
        $this->assertContains($teacher->id, $users->get_userids());

        $approved = new approved_userlist($context, 'mod_videopredict', [$teacher->id]);
        provider::delete_data_for_users($approved);

        $response = $DB->get_record('videopredict_responses', ['id' => $responseid], '*', MUST_EXIST);
        $this->assertEquals(0, $response->gradedby);
        $this->assertEquals($student->id, $response->userid);
        $this->assertSame('Answer', $response->response);
    }
}
