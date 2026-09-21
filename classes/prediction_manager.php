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

namespace mod_videopredict;

/**
 * Prediction point business logic.
 *
 * @package mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prediction_manager {
    /**
     * Get point or fail.
     */
    public function get_point(int $pointid, int $activityid = 0): \stdClass {
        global $DB;
        $conditions = ['id' => $pointid];
        if ($activityid) {
            $conditions['videopredictid'] = $activityid;
        }
        return $DB->get_record('videopredict_points', $conditions, '*', MUST_EXIST);
    }

    /**
     * Return decoded choices.
     */
    public function choices(\stdClass $point): array {
        $decoded = json_decode((string)$point->optionsjson, true);
        if (!is_array($decoded)) {
            return [];
        }
        $choices = [];
        foreach ($decoded as $key => $label) {
            if (is_array($label)) {
                $key = (string)($label['value'] ?? $key);
                $label = (string)($label['label'] ?? $key);
            }
            $choices[(string)$key] = (string)$label;
        }
        return $choices;
    }

    /**
     * Normalize teacher-entered one-option-per-line text into JSON.
     */
    public function options_to_json(string $text): ?string {
        $lines = preg_split('/\R/u', trim($text)) ?: [];
        $options = [];
        $index = 1;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^([^|]+)\|(.*)$/u', $line, $match)) {
                $key = trim($match[1]);
                $label = trim($match[2]);
            } else {
                $key = (string)$index;
                $label = $line;
            }
            if ($key !== '' && $label !== '') {
                $options[$key] = $label;
                $index++;
            }
        }
        return $options ? json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    }

    /**
     * Convert options JSON back to editable lines.
     */
    public function options_to_text(?string $json): string {
        $options = json_decode((string)$json, true);
        if (!is_array($options)) {
            return '';
        }
        $lines = [];
        foreach ($options as $key => $label) {
            $lines[] = $key . '|' . $label;
        }
        return implode("\n", $lines);
    }

    /**
     * Save immutable initial prediction.
     */
    public function submit_prediction(\stdClass $activity, \stdClass $point, int $userid, string $response): \stdClass {
        global $DB;
        $existing = $DB->get_record('videopredict_responses', ['pointid' => $point->id, 'userid' => $userid]);
        if ($existing) {
            throw new \moodle_exception('predictionlocked', 'videopredict');
        }
        $progress = (new progress_manager())->get_progress($activity->id, $userid);
        if ((float)$progress->maxwatched + 5.0 < (float)$point->timeposition) {
            throw new \moodle_exception('pointnotreached', 'videopredict');
        }
        $response = trim($response);
        if ($response === '') {
            throw new \moodle_exception('responseisrequired', 'videopredict');
        }
        $iscorrect = -1;
        $awarded = 0.0;
        if ($point->responsetype !== 'open') {
            $choices = $this->choices($point);
            if (!array_key_exists($response, $choices)) {
                throw new \moodle_exception('invalidresponse', 'videopredict');
            }
            if ((string)$point->correctanswer !== '') {
                $iscorrect = hash_equals((string)$point->correctanswer, $response) ? 1 : 0;
                $awarded = $iscorrect ? (float)$point->points : 0.0;
            }
        }
        $now = time();
        $record = (object)[
            'pointid' => $point->id,
            'userid' => $userid,
            'response' => $response,
            'responsejson' => null,
            'iscorrect' => $iscorrect,
            'awarded' => $awarded,
            'reflection' => null,
            'understandingchanged' => -1,
            'predictiontime' => $now,
            'reflectiontime' => 0,
            'gradedby' => 0,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videopredict_responses', $record);
        (new progress_manager())->sync_user($activity, $userid);
        return $record;
    }

    /**
     * Save reflection only after the reveal position has been reached.
     */
    public function submit_reflection(\stdClass $activity, \stdClass $point, int $userid,
                                      string $reflection, int $changed): \stdClass {
        global $DB;
        $record = $DB->get_record('videopredict_responses', ['pointid' => $point->id, 'userid' => $userid], '*', MUST_EXIST);
        if ((string)$record->reflection !== '') {
            throw new \moodle_exception('reflectionlocked', 'videopredict');
        }
        $progress = (new progress_manager())->get_progress($activity->id, $userid);
        if ((float)$progress->maxwatched + 2.0 < (float)$point->revealposition) {
            throw new \moodle_exception('resultnotreached', 'videopredict');
        }
        $reflection = trim($reflection);
        if ($reflection === '' && trim((string)$point->reflectionquestion) !== '') {
            throw new \moodle_exception('responseisrequired', 'videopredict');
        }
        $record->reflection = $reflection;
        $record->understandingchanged = in_array($changed, [0, 1], true) ? $changed : -1;
        $record->reflectiontime = time();
        $record->timemodified = time();
        $DB->update_record('videopredict_responses', $record);
        return $record;
    }

    /**
     * Build safe student point state. Result content is hidden until reached.
     */
    public function student_state(\stdClass $activity, int $userid): array {
        global $DB;
        $progressmanager = new progress_manager();
        $progress = $progressmanager->get_progress($activity->id, $userid);
        $points = $DB->get_records('videopredict_points',
            ['videopredictid' => $activity->id], 'timeposition ASC, sortorder ASC, id ASC');
        $byid = [];
        if ($points) {
            [$insql, $params] = $DB->get_in_or_equal(array_keys($points), SQL_PARAMS_NAMED, 'point');
            $params['userid'] = $userid;
            $responses = $DB->get_records_select('videopredict_responses', "userid = :userid AND pointid {$insql}", $params);
            foreach ($responses as $response) {
                $byid[$response->pointid] = $response;
            }
        }
        $state = [];
        foreach ($points as $point) {
            $response = $byid[$point->id] ?? null;
            $revealed = $response && (float)$progress->maxwatched + 2.0 >= (float)$point->revealposition;
            $choices = [];
            foreach ($this->choices($point) as $value => $label) {
                $choices[] = ['value' => $value, 'label' => $label];
            }
            $state[] = [
                'id' => (int)$point->id,
                'timeposition' => (float)$point->timeposition,
                'revealposition' => (float)$point->revealposition,
                'title' => format_string($point->title),
                'question' => format_text($point->question, $point->questionformat, ['para' => false]),
                'responsetype' => $point->responsetype,
                'choices' => $choices,
                'required' => (bool)$point->required,
                'answered' => (bool)$response,
                'response' => $response ? (string)$response->response : '',
                'revealed' => (bool)$revealed,
                'resulttext' => $revealed ? format_text((string)$point->resulttext, $point->resultformat, ['para' => false]) : '',
                'reflectionquestion' => $revealed ?
                    format_text((string)$point->reflectionquestion, $point->reflectionformat, ['para' => false]) : '',
                'reflected' => $response && (string)$response->reflection !== '',
                'reflection' => $response ? (string)$response->reflection : '',
                'understandingchanged' => $response ? (int)$response->understandingchanged : -1,
                'iscorrect' => $revealed && $response ? (int)$response->iscorrect : -1,
                'pauseonreveal' => (bool)$point->pauseonreveal,
            ];
        }
        return [
            'points' => $state,
            'progress' => [
                'percent' => (float)$progress->percent,
                'lastposition' => (float)$progress->lastposition,
                'maxwatched' => (float)$progress->maxwatched,
                'duration' => (float)$progress->duration,
                'maxposition' => $progressmanager->max_allowed_position($activity, $userid, $progress),
                'completed' => (bool)$progress->completed,
            ],
        ];
    }
}
