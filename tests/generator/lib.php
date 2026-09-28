<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Video Prediction test data generator.
 *
 * @package   mod_videopredict
 * @category  test
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Video Prediction test data generator.
 */
class mod_videopredict_generator extends testing_module_generator {
    /**
     * Create an activity instance.
     *
     * @param array|stdClass|null $record Instance data.
     * @param array|null $options Generator options.
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;
        $defaults = [
            'name' => 'Video Prediction test',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'videosource' => 'url',
            'videourl' => 'https://example.com/video.mp4',
            'posterurl' => '',
            'preventseek' => 1,
            'resumeplayback' => 1,
            'completionpredictions' => 0,
            'completionpercent' => 0,
            'grademode' => 'predictions',
            'grade' => 100,
        ];
        foreach ($defaults as $key => $value) {
            if (!isset($record->{$key})) {
                $record->{$key} = $value;
            }
        }
        return parent::create_instance($record, (array)$options);
    }
}
