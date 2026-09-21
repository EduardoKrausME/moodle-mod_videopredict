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
 * manual_grade_form.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videopredict\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("{$CFG->libdir}/formslib.php");

/**
 * Manual grade form for open responses.
 */
class manual_grade_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'responseid');
        $mform->setType('responseid', PARAM_INT);
        $mform->addElement('select', 'iscorrect', get_string('markcorrectness', 'videopredict'), [
            -1 => get_string('notgraded', 'videopredict'),
            0 => get_string('incorrect', 'videopredict'),
            1 => get_string('correct', 'videopredict'),
        ]);
        $mform->addElement('text', 'awarded', get_string('awardedpoints', 'videopredict'), ['size' => 8]);
        $mform->setType('awarded', PARAM_FLOAT);
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
