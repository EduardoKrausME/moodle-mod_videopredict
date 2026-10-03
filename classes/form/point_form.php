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
 * point_form.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\form;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("{$CFG->libdir}/formslib.php");

/**
 * Prediction point editor.
 */
class point_form extends \moodleform {
    /**
     * Define form.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'pointid');
        $mform->setType('pointid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('pointtitle', 'videopredict'), ['size' => 60]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addElement('text', 'timepositiontext', get_string('predictiontimeposition', 'videopredict'), ['size' => 12]);
        $mform->setType('timepositiontext', PARAM_TEXT);
        $mform->addRule('timepositiontext', null, 'required', null, 'client');
        $mform->addElement('editor', 'question_editor', get_string('predictionquestion', 'videopredict'), null,
            ['maxfiles' => 0, 'context' => $this->_customdata['context']]);
        $mform->addRule('question_editor', null, 'required', null, 'client');

        $mform->addElement('select', 'responsetype', get_string('responsetype', 'videopredict'), [
            'open' => get_string('responseopen', 'videopredict'),
            'multichoice' => get_string('responsemultiplechoice', 'videopredict'),
            'truefalse' => get_string('responsetruefalse', 'videopredict'),
            'outcomes' => get_string('responseoutcomes', 'videopredict'),
        ]);
        if (!empty($this->_customdata['hasresponses'])) {
            $mform->freeze('responsetype');
        }
        $mform->addElement('textarea', 'optionstext', get_string('responseoptions', 'videopredict'), ['rows' => 6, 'cols' => 70]);
        $mform->setType('optionstext', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('optionstext', 'responseoptions', 'videopredict');
        $mform->hideIf('optionstext', 'responsetype', 'eq', 'open');
        $mform->hideIf('optionstext', 'responsetype', 'eq', 'truefalse');
        $mform->addElement('text', 'correctanswer', get_string('correctanswer', 'videopredict'), ['size' => 30]);
        $mform->setType('correctanswer', PARAM_TEXT);
        $mform->hideIf('correctanswer', 'responsetype', 'eq', 'open');
        $mform->hideIf('correctanswer', 'responsetype', 'eq', 'truefalse');
        $mform->addHelpButton('correctanswer', 'correctanswer', 'videopredict');
        $mform->addElement('select', 'correctanswertruefalse', get_string('correctanswer', 'videopredict'), [
            '' => get_string('notgraded', 'videopredict'),
            'true' => get_string('true', 'videopredict'),
            'false' => get_string('false', 'videopredict'),
        ]);
        $mform->hideIf('correctanswertruefalse', 'responsetype', 'neq', 'truefalse');

        $mform->addElement('advcheckbox', 'required', get_string('predictionrequired', 'videopredict'));
        $mform->setDefault('required', 1);
        $mform->addElement('text', 'points', get_string('points', 'videopredict'), ['size' => 8]);
        $mform->setType('points', PARAM_FLOAT);
        $mform->setDefault('points', 1);

        $mform->addElement('html', '<h3>' . get_string('resultheader', 'videopredict') . '</h3>');
        $mform->addElement('text', 'revealpositiontext', get_string('revealposition', 'videopredict'), ['size' => 12]);
        $mform->setType('revealpositiontext', PARAM_TEXT);
        $mform->addHelpButton('revealpositiontext', 'revealposition', 'videopredict');
        $mform->addElement('editor', 'result_editor', get_string('resulttext', 'videopredict'), null,
            ['maxfiles' => 0, 'context' => $this->_customdata['context']]);
        $mform->addElement('editor', 'reflection_editor', get_string('reflectionquestion', 'videopredict'), null,
            ['maxfiles' => 0, 'context' => $this->_customdata['context']]);
        $mform->addElement('advcheckbox', 'pauseonreveal', get_string('pauseonreveal', 'videopredict'));
        $mform->addElement('text', 'sortorder', get_string('sortorder', 'videopredict'), ['size' => 6]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validate.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        foreach (['timepositiontext', 'revealpositiontext'] as $field) {
            if ($field === 'revealpositiontext' && trim((string)($data[$field] ?? '')) === '') {
                continue;
            }
            if (!preg_match('/^(?:(\d+):)?([0-5]?\d):(\d{2})(?:\.(\d+))?$|^\d+(?:\.\d+)?$/', trim((string)($data[$field] ?? '')))) {
                $errors[$field] = get_string('invalidtimecode', 'videopredict');
            }
        }
        if ((float)($data['points'] ?? 0) < 0) {
            $errors['points'] = get_string('invalidgrade', 'videopredict');
        }

        $type = (string)($data['responsetype'] ?? 'open');
        if (in_array($type, ['multichoice', 'outcomes'], true)) {
            $manager = new \mod_videopredict\prediction_manager();
            $optionsjson = $manager->options_to_json((string)($data['optionstext'] ?? ''));
            $options = json_decode((string)$optionsjson, true);
            if (!$options) {
                $errors['optionstext'] = get_string('optionsrequired', 'videopredict');
            } else {
                $correctanswer = trim((string)($data['correctanswer'] ?? ''));
                if ($correctanswer !== '' && !array_key_exists($correctanswer, $options)) {
                    $errors['correctanswer'] = get_string('invalidcorrectanswer', 'videopredict');
                }
            }
        }

        $parse = static function (string $value): ?float {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            if (is_numeric($value)) {
                return (float)$value;
            }
            if (!preg_match('/^(?:(\\d+):)?([0-5]?\\d):(\\d{2})(?:\\.(\\d+))?$/', $value, $matches)) {
                return null;
            }
            $hours = isset($matches[1]) && $matches[1] !== '' ? (float)$matches[1] : 0.0;
            $minutes = (float)$matches[2];
            $seconds = (float)$matches[3];
            if (!empty($matches[4])) {
                $seconds += (float)('0.' . $matches[4]);
            }
            return $hours * 3600 + $minutes * 60 + $seconds;
        };
        $predictiontime = $parse((string)($data['timepositiontext'] ?? ''));
        $revealtime = $parse((string)($data['revealpositiontext'] ?? ''));
        if ($predictiontime !== null && $revealtime !== null && $revealtime < $predictiontime) {
            $errors['revealpositiontext'] = get_string('revealbeforeprediction', 'videopredict');
        }

        return $errors;
    }
}
