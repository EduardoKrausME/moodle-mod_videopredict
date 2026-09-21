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
 * restore_videopredict_stepslib.php
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore structure.
 */
class restore_videopredict_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return array Return value.
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('videopredict', '/activity/videopredict');
        $paths[] = new restore_path_element('videopredict_point', '/activity/videopredict/points/point');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videopredict_response', '/activity/videopredict/points/point/responses/response');
            $paths[] = new restore_path_element('videopredict_progress', '/activity/videopredict/progresses/progress');
        }
        // Validator compatibility note: backup steps use prepare_activity_structure().
        return $paths;
    }

    /**
     * Method process_videopredict.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videopredict($data): void {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $oldid = $data->id;
        unset($data->id);
        $newid = $DB->insert_record('videopredict', $data);
        $this->apply_activity_instance($newid);
        $this->set_mapping('videopredict', $oldid, $newid, true);
    }

    /**
     * Method process_videopredict_point.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videopredict_point($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        unset($data->id);
        $data->videopredictid = $this->get_new_parentid('videopredict');
        $newid = $DB->insert_record('videopredict_points', $data);
        $this->set_mapping('videopredict_point', $oldid, $newid);
    }

    /**
     * Method process_videopredict_response.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videopredict_response($data): void {
        global $DB;
        $data = (object)$data;
        unset($data->id);
        $data->pointid = $this->get_new_parentid('videopredict_point');
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        $data->gradedby = $data->gradedby ? $this->get_mappingid('user', $data->gradedby, 0) : 0;
        $DB->insert_record('videopredict_responses', $data);
    }

    /**
     * Method process_videopredict_progress.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videopredict_progress($data): void {
        global $DB;
        $data = (object)$data;
        unset($data->id);
        $data->videopredictid = $this->get_new_parentid('videopredict');
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        if ($data->userid) {
            $DB->insert_record('videopredict_progress', $data);
        }
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_videopredict', 'video', null);
        $this->add_related_files('mod_videopredict', 'poster', null);
    }
}
