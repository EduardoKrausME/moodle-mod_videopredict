<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Tests for prediction and progress business logic.
 *
 * @package   mod_videopredict
 * @category  test
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict;

/**
 * Tests for prediction and progress business logic.
 */
final class progress_manager_test extends \advanced_testcase {
    /**
     * Create a prediction point.
     *
     * @param int $activityid Activity id.
     * @param array $overrides Field overrides.
     * @return stdClass
     */
    private function create_point(int $activityid, array $overrides = []): \stdClass {
        global $DB;

        $record = (object)array_merge([
            'videopredictid' => $activityid,
            'timeposition' => 0,
            'revealposition' => 10,
            'title' => 'Prediction',
            'question' => '<p>What happens next?</p>',
            'questionformat' => FORMAT_HTML,
            'responsetype' => 'multichoice',
            'optionsjson' => json_encode(['A' => 'Option A', 'B' => 'Option B']),
            'correctanswer' => 'A',
            'resulttext' => '<p>Result</p>',
            'resultformat' => FORMAT_HTML,
            'reflectionquestion' => '',
            'reflectionformat' => FORMAT_HTML,
            'required' => 1,
            'pauseonreveal' => 0,
            'points' => 2,
            'sortorder' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ], $overrides);
        $record->id = $DB->insert_record('videopredict_points', $record);
        return $record;
    }

    /**
     * Create progress that has already reached the supplied position.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @param float $position Watched position.
     * @return stdClass
     */
    private function create_progress(int $activityid, int $userid, float $position): \stdClass {
        global $DB;

        $record = (object)[
            'videopredictid' => $activityid,
            'userid' => $userid,
            'duration' => 120,
            'lastposition' => $position,
            'maxwatched' => $position,
            'uniquewatched' => $position,
            'totalwatchtime' => $position,
            'percent' => $position / 1.2,
            'segments' => json_encode([[0, $position]]),
            'completed' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $record->id = $DB->insert_record('videopredict_progress', $record);
        return $record;
    }

    /**
     * Predictions are immutable and objective responses are regraded when the point changes.
     */
    public function test_prediction_is_immutable_and_regraded(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $point = $this->create_point($activity->id);
        $this->create_progress($activity->id, $user->id, 20);

        $manager = new prediction_manager();
        $response = $manager->submit_prediction($activity, $point, $user->id, 'A');
        $this->assertEquals(1, $response->iscorrect);
        $this->assertEquals(2.0, (float)$response->awarded);

        try {
            $manager->submit_prediction($activity, $point, $user->id, 'B');
            $this->fail('A second immutable prediction should not be accepted.');
        } catch (\moodle_exception $exception) {
            $this->assertEquals('predictionlocked', $exception->errorcode);
        }

        $point->correctanswer = 'B';
        $point->points = 4;
        $DB->update_record('videopredict_points', $point);
        $manager->regrade_point($point);

        $response = $DB->get_record('videopredict_responses', ['id' => $response->id], '*', MUST_EXIST);
        $this->assertEquals(0, $response->iscorrect);
        $this->assertEquals(0.0, (float)$response->awarded);

        $point->correctanswer = 'A';
        $DB->update_record('videopredict_points', $point);
        $manager->regrade_point($point);
        $response = $DB->get_record('videopredict_responses', ['id' => $response->id], '*', MUST_EXIST);
        $this->assertEquals(1, $response->iscorrect);
        $this->assertEquals(4.0, (float)$response->awarded);
    }

    /**
     * Future prediction questions are not returned before the learner approaches the point.
     */
    public function test_future_questions_are_hidden(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $point = $this->create_point($activity->id, ['timeposition' => 60, 'revealposition' => 70]);
        $progress = $this->create_progress($activity->id, $user->id, 0);

        $manager = new prediction_manager();
        $state = $manager->student_state($activity, $user->id);
        $this->assertSame('', $state['points'][0]['question']);
        $this->assertSame([], $state['points'][0]['choices']);

        $progress->maxwatched = 56;
        $progress->lastposition = 56;
        $progress->timemodified = time();
        $DB->update_record('videopredict_progress', $progress);

        $state = $manager->student_state($activity, $user->id);
        $this->assertNotSame('', $state['points'][0]['question']);
        $this->assertCount(2, $state['points'][0]['choices']);
        $this->assertEquals($point->id, $state['points'][0]['id']);
    }

    /**
     * Course reset removes learner data but leaves the activity and prediction points.
     */
    public function test_course_reset_removes_user_data(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        require_once($CFG->dirroot . '/mod/videopredict/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $point = $this->create_point($activity->id);
        $this->create_progress($activity->id, $user->id, 20);
        (new prediction_manager())->submit_prediction($activity, $point, $user->id, 'A');

        videopredict_reset_userdata((object)[
            'courseid' => $course->id,
            'reset_videopredict' => 1,
        ]);

        $this->assertEquals(0, $DB->count_records('videopredict_progress', ['videopredictid' => $activity->id]));
        $this->assertEquals(0, $DB->count_records('videopredict_responses', ['pointid' => $point->id]));
        $this->assertTrue($DB->record_exists('videopredict_points', ['id' => $point->id]));
        $this->assertTrue($DB->record_exists('videopredict', ['id' => $activity->id]));
    }

    /**
     * A point from another Video Prediction activity is never accepted.
     */
    public function test_point_lookup_is_scoped_to_activity(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity1 = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $activity2 = $this->getDataGenerator()->create_module('videopredict', ['course' => $course->id]);
        $point = $this->create_point($activity2->id);

        $this->expectException(\dml_missing_record_exception::class);
        (new prediction_manager())->get_point($point->id, $activity1->id);
    }

    /**
     * Completion changes when required predictions are submitted.
     */
    public function test_required_prediction_completion(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videopredict', [
            'course' => $course->id,
            'completionpredictions' => 1,
        ]);
        $point = $this->create_point($activity->id);
        $this->create_progress($activity->id, $user->id, 20);

        $manager = new progress_manager();
        $this->assertFalse($manager->is_complete($activity, $user->id));

        (new prediction_manager())->submit_prediction($activity, $point, $user->id, 'A');
        $this->assertTrue($manager->is_complete($activity, $user->id));
    }
}
