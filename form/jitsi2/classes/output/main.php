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
 * Class for Plenary meeting media elements
 *
 * @package     plenumform_jitsi2
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plenumform_jitsi2\output;

use cache;
use cm_info;
use context_module;
use moodle_url;
use MoodleQuickForm;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use mod_plenum\motion;
use mod_plenum\output\motions;

/**
 * Class for Plenary meeting media elements
 *
 * @copyright   2024 Daniel Thies <dethies@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main extends \mod_plenum\output\main {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $motions = new motions($this->context);

        return [
            'chair' => has_capability('mod/plenum:preside', $this->context),
            'delay' => get_config('plenumform_jitsi2', 'delay') * 1000,
            'email' => $USER->email,
            'fullname' => fullname($USER),
            'jwt' => $this->get_jwt(),
            'room' => $this->get_room(),
            'server' => get_config('plenumform_jitsi2', 'server'),
            'throttle' => get_config('block_deft', 'throttle'),
            'slots' => [
                [
                    'slotname' => get_string('chair', 'plenumform_deft'),
                    'slot' => 'chair',
                    'posterurl' => $output->image_url('chair-solid', 'plenumform_deft'),
                ],
                [
                    'slotname' => get_string('floor', 'plenumform_deft'),
                    'slot' => 'speaker',
                    'posterurl' => $output->image_url('microphone-solid', 'plenumform_deft'),
                ],
            ],
        ] + $motions->export_for_template($output) + parent::export_for_template($output);
    }

    /**
     * Return the room key
     *
     * @return string
     */
    protected function get_room() {
        global $DB;

        if (
            groups_get_activity_groupmode($this->cm)
            && ($pendingmotions = motion::get_pending($this->context))
            && $room = array_pop($pendingmotions)->get_data()->room ?? ''
        ) {
            return $room;
        }

        return $DB->get_field('plenumform_jitsi2', 'room', ['plenum' => $this->cm->instance]);
    }

    /**
     * Return the jwt
     *
     * @return string
     */
    protected function get_jwt() {
        global $USER;

        $header = json_encode([
            "alg" => "HS256",
            "kid" => "jitsi2/custom_key_name",
            "typ" => "JWT",
        ], JSON_UNESCAPED_SLASHES);
        $payload = json_encode([
            'aud' => 'jitsi2',
            'context' => [
                'user' => [
                    'id' => $USER->username,
                    'name' => fullname($USER),
                    'email' => $USER->email,
                ],
            ],
            'exp' => time() + DAYSECS,
            'iss' => get_config('plenumform_jitsi2', 'appid'),
            'moderator' => has_capability('mod/plenum:preside', $this->context),
            'sub' => get_config('plenumform_jitsi2', 'server'),
            'room' => $this->get_room(),
        ], JSON_UNESCAPED_SLASHES);
        $message = $this->encode($header) . '.' . $this->encode($payload);
        return $message . '.' . $this->encode(hash_hmac('SHA256', $message, get_config('plenumform_jitsi2', 'secret'), true));
    }

    /**
     * Encode content for jwt
     *
     * @param string $content
     * @return string
     */
    protected function encode($content) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($content));
    }

    /**
     * Called mform mod_form after_data to add form specific options
     *
     * @param MoodleQuickForm $mform Form to which to add fields
     */
    public static function create_settings_elements(MoodleQuickForm $mform) {
        $mform->insertElementBefore(
            $mform->createElement('text', 'room', get_string('room', 'plenumform_jitsi2')),
            'addformoptionshere'
        );
        $mform->setType('room', PARAM_ALPHA);
    }
}
