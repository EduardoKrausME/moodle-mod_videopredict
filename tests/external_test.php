<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * External service tests.
 *
 * @package   mod_videopredict
 * @category  test
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict;

/**
 * External service tests.
 */
final class external_test extends \advanced_testcase {
    /**
     * Exercise the complete learner AJAX workflow.
     */
    public function test_learner_external_workflow(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $activity = $this->getDataGenerator()->create_module('videopredict', [
            'course' => $course->id,
            'completionpredictions' => 1,
        ]);
        $cm = get_coursemodule_from_instance('videopredict', $activity->id, $course->id, false, MUST_EXIST);

        $pointid = $DB->insert_record('videopredict_points', (object)[
            'videopredictid' => $activity->id,
            'timeposition' => 0,
            'revealposition' => 0,
            'title' => 'Prediction',
            'question' => '<p>Choose.</p>',
            'questionformat' => FORMAT_HTML,
            'responsetype' => 'multichoice',
            'optionsjson' => json_encode(['A' => 'A', 'B' => 'B']),
            'correctanswer' => 'A',
            'resulttext' => '<p>A is correct.</p>',
            'resultformat' => FORMAT_HTML,
            'reflectionquestion' => 'Why?',
            'reflectionformat' => FORMAT_PLAIN,
            'required' => 1,
            'pauseonreveal' => 1,
            'points' => 1,
            'sortorder' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $this->setUser($student);

        $progress = \mod_videopredict\external\update_progress::execute(
            $cm->id,
            0.0,
            0.5,
            0.5,
            30.0,
            1.0
        );
        $this->assertGreaterThanOrEqual(0, $progress['percent']);

        $submitted = \mod_videopredict\external\submit_prediction::execute($cm->id, $pointid, 'A');
        $this->assertEquals(1, $submitted['iscorrect']);

        $state = \mod_videopredict\external\get_state::execute($cm->id);
        $this->assertTrue($state['points'][0]['answered']);
        $this->assertTrue($state['points'][0]['revealed']);

        $reflection = \mod_videopredict\external\submit_reflection::execute(
            $cm->id,
            $pointid,
            'Because the evidence points to A.',
            1
        );
        $this->assertTrue($reflection['saved']);

        $stored = $DB->get_record(
            'videopredict_responses',
            ['pointid' => $pointid, 'userid' => $student->id],
            '*',
            MUST_EXIST
        );
        $this->assertSame('Because the evidence points to A.', $stored->reflection);
        $this->assertEquals(1, $stored->understandingchanged);
    }
}
