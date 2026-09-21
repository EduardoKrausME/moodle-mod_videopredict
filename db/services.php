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
 * External functions.
 *
 * @package mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_videopredict_update_progress' => [
        'classname' => 'mod_videopredict\\external\\update_progress',
        'methodname' => 'execute',
        'description' => 'Update watched video progress.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/videopredict:view',
    ],
    'mod_videopredict_submit_prediction' => [
        'classname' => 'mod_videopredict\\external\\submit_prediction',
        'methodname' => 'execute',
        'description' => 'Store an immutable prediction response.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/videopredict:view',
    ],
    'mod_videopredict_submit_reflection' => [
        'classname' => 'mod_videopredict\\external\\submit_reflection',
        'methodname' => 'execute',
        'description' => 'Store a post-result reflection.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/videopredict:view',
    ],
    'mod_videopredict_get_state' => [
        'classname' => 'mod_videopredict\\external\\get_state',
        'methodname' => 'execute',
        'description' => 'Return current prediction/player state.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/videopredict:view',
    ],
];
