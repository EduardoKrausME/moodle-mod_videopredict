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
 * Activity settings form.
 *
 * @package mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Activity form.
 */
class mod_videopredict_mod_form extends moodleform_mod {
    /**
     * Define form.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('videopredictname', 'videopredict'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('header', 'videoheader', get_string('videoheader', 'videopredict'));
        $sources = [
            'upload' => get_string('sourceupload', 'videopredict'),
            'url' => get_string('sourceurl', 'videopredict'),
            'youtube' => get_string('sourceyoutube', 'videopredict'),
            'vimeo' => get_string('sourcevimeo', 'videopredict'),
        ];
        $mform->addElement('select', 'videosource', get_string('videosource', 'videopredict'), $sources);
        $mform->setDefault('videosource', 'url');

        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'videopredict'), null, [
            'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video'],
        ]);
        $mform->hideIf('videofile', 'videosource', 'neq', 'upload');

        $mform->addElement('text', 'videourl', get_string('videourl', 'videopredict'), ['size' => 80]);
        $mform->setType('videourl', PARAM_RAW_TRIMMED);
        $mform->hideIf('videourl', 'videosource', 'eq', 'upload');
        $mform->addHelpButton('videourl', 'videourl', 'videopredict');

        $mform->addElement('filemanager', 'poster', get_string('poster', 'videopredict'), null, [
            'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image'],
        ]);
        $mform->addElement('text', 'posterurl', get_string('posterurl', 'videopredict'), ['size' => 80]);
        $mform->setType('posterurl', PARAM_URL);

        $mform->addElement('advcheckbox', 'preventseek', get_string('preventseek', 'videopredict'));
        $mform->setDefault('preventseek', 1);
        $mform->addHelpButton('preventseek', 'preventseek', 'videopredict');
        $mform->addElement('advcheckbox', 'resumeplayback', get_string('resumeplayback', 'videopredict'));
        $mform->setDefault('resumeplayback', 1);

        $mform->addElement('header', 'gradeheader', get_string('gradeheader', 'videopredict'));
        $mform->addElement('select', 'grademode', get_string('grademode', 'videopredict'), [
            'predictions' => get_string('grademodepredictions', 'videopredict'),
            'progress' => get_string('grademodeprogress', 'videopredict'),
            'blended' => get_string('grademodeblended', 'videopredict'),
        ]);
        $mform->setDefault('grademode', 'predictions');
        $mform->addElement('text', 'grade', get_string('maximumgrade', 'videopredict'), ['size' => 8]);
        $mform->setType('grade', PARAM_FLOAT);
        $mform->setDefault('grade', 100);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Custom completion fields.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $mform->addElement('advcheckbox', 'completionpredictions', get_string('completionpredictions', 'videopredict'));
        $mform->setDefault('completionpredictions', 1);
        $mform->addElement('text', 'completionpercent', get_string('completionpercent', 'videopredict'), ['size' => 4]);
        $mform->setType('completionpercent', PARAM_INT);
        $mform->setDefault('completionpercent', 0);
        return ['completionpredictions', 'completionpercent'];
    }

    /**
     * Whether completion rules are enabled.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionpredictions']) || !empty($data['completionpercent']);
    }

    /**
     * Validate settings.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (($data['videosource'] ?? 'url') !== 'upload' && trim((string)($data['videourl'] ?? '')) === '') {
            $errors['videourl'] = get_string('required');
        }
        if (isset($data['completionpercent']) && ((int)$data['completionpercent'] < 0 || (int)$data['completionpercent'] > 100)) {
            $errors['completionpercent'] = get_string('invalidpercent', 'videopredict');
        }
        if (isset($data['grade']) && (float)$data['grade'] < 0) {
            $errors['grade'] = get_string('invalidgrade', 'videopredict');
        }
        return $errors;
    }

    /**
     * Prepare draft files on edit.
     */
    public function set_data($defaultvalues): void {
        if (!empty($defaultvalues->instance)) {
            $cm = get_coursemodule_from_instance('videopredict', $defaultvalues->instance, 0, false, IGNORE_MISSING);
            if ($cm) {
                $defaultvalues = videopredict_prepare_editing_data($defaultvalues, context_module::instance($cm->id));
            }
        }
        parent::set_data($defaultvalues);
    }
}
