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
 * @package     plenumform_jitsi
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace plenumform_jitsi\output;

use cache;
use cm_info;
use context_module;
use moodle_url;
use MoodleQuickForm;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use user_picture;
use mod_plenum\motion;
use mod_plenum\output\motions;

/**
 * Class for Plenary meeting media elements
 *
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
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
            'delay' => get_config('plenumform_jitsi', 'delay') * 1000,
            'options' => $this->get_options(),
            'server' => get_config('plenumform_jitsi', 'server'),
            'width' => (int)get_config('plenumform_jitsi', 'width'),
        ] + $motions->export_for_template($output) + parent::export_for_template($output);
    }

    /**
     * Get Jitsi meet room options
     *
     * @return string JSON string
     */
    protected function get_options() {
        global $USER;

        $options = [
            'jwt' => $this->get_jwt(),
            'server' => get_config('plenumform_jitsi', 'server'),
            'roomName' => $this->get_room(),
            'configOverwrite' => [
                'hideConferenceSubject' => true,
                'lobby' => ['autoknock' => true],
                'startWithAudioMuted' => true,
                'startWithVideoMuted' => true,
                'readOnlyName' => true,
                'toolbarButtons' => explode(',', get_config('plenumform_jitsi', 'toolbar')),
            ],
            'securityUI' => ['hideLobbyButton' => true],
            'height' => '56.25vw',
            'userInfo' => [
                'displayName' => fullname($USER),
                'email' => $USER->email,
            ],
        ];

        return json_encode($options);
    }

    /**
     * Return the room key
     *
     * @param string $type Key type
     * @return string
     */
    protected function get_room(string $type = 'main') {
        global $CFG, $DB;

        if (
            groups_get_activity_groupmode($this->cm)
        ) {
            $groupid = groups_get_activity_group($this->cm);
            return md5("$CFG->wwwroot mod {$this->cm->instance} group {$groupid} type $type");
        }

        return md5("$CFG->wwwroot mod {$this->cm->instance}");
    }

    /**
     * Return the jwt
     *
     * @return string
     */
    protected function get_jwt() {
        global $OUTPUT, $PAGE, $USER;

        $userpicture = new user_picture($USER, ['size' => 256]);

        $header = json_encode([
            "alg" => "HS256",
            "kid" => "jitsi/custom_key_name",
            "typ" => "JWT",
        ], JSON_UNESCAPED_SLASHES);
        $payload = json_encode([
            'aud' => 'jitsi',
            'context' => [
                'user' => [
                    'id' => $USER->username,
                    'name' => fullname($USER),
                    'email' => $USER->email,
                    'avatar' => $userpicture->get_url($PAGE, $OUTPUT)->out(false),
                    'moderator' => has_capability('mod/plenum:preside', $this->context),
                ],
            ],
            'exp' => time() + DAYSECS,
            'iss' => get_config('plenumform_jitsi', 'appid'),
            'sub' => get_config('plenumform_jitsi', 'server'),
            'room' => $this->get_room(),
        ], JSON_UNESCAPED_SLASHES);
        $message = $this->encode($header) . '.' . $this->encode($payload);
        return $message . '.' . $this->encode(hash_hmac('SHA256', $message, get_config('plenumform_jitsi', 'secret'), true));
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
    }
}
