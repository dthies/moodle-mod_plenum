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

namespace plenumform_jitsi2\external;

use mod_plenum\motion;
use mod_plenum\plenum;
use context;
use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;
use mod_plenum\output\motions;
use plenumform_deft\output\user_picture;

/**
 * External updating meeting information
 *
 * @package    plenumform_jitsi2
 * @copyright  2024 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_content extends external_api {
    /**
     * Get parameter definition for get room
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Context id for Plenary meeting activity module'),
            ]
        );
    }

    /**
     * Get update meeting information
     *
     * @param int $contextid Plenary meeting module context id
     * @return array
     */
    public static function execute(int $contextid): array {
        global $DB, $OUTPUT, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);

        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        $plenum = \core\di::get(\mod_plenum\manager::class)->get_plenum($context);

        $motions = new motions($context);

        $pending = motion::immediate_pending($context);
        $floor = empty($pending) ? 0 : $pending->get('usercreated');
        $groupid = empty($pending) ? 0 : $pending->get('groupid');

        $hasfloor = ($floor == $USER->id);
        $canshare = has_capability('mod/plenum:meet', $context)
            && !empty($pending)
            && has_capability('plenumform/deft:sharevideo', $context)
            && (
                has_capability('mod/plenum:preside', $context)
                || $hasfloor
            );
        $issharingvideo = $canshare && $DB->get_record_sql(
            "SELECT s.*
               FROM {plenumform_jitsi2_speaker} s
               JOIN {plenum_motion} m ON s.motion = m.id
              WHERE m.groupid = :groupid
                    AND s.status = :status
                    AND s.plenum = :plenum
                    AND s.usermodified = :usermodified",
            [
            'plenum' => $plenum->get_id(),
            'status' => 0,
            'groupid' => $groupid,
            'usermodified' => $USER->id,
            ]
        );
        $controls = $OUTPUT->render_from_template('plenumform_jitsi2/controls', [
            'sharevideo' => $canshare,
            'issharingvideo' => $issharingvideo,
        ]);

        return [
            'controls' => $controls,
            'motions' => $OUTPUT->render($motions),
            'javascript' => '',
            'sharevideo' => $canshare,
            'userinfo' => self::userinfo($plenum, $pending),
        ];
    }

    /**
     * Get return definition
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'controls' => new external_value(PARAM_RAW, 'HTML for video controls'),
            'motions' => new external_value(PARAM_RAW, 'HTML for motions'),
            'javascript' => new external_value(PARAM_RAW, 'Javascript to be executed after loading'),
            'sharevideo' => new external_value(PARAM_BOOL, 'Whether user can share video'),
            'userinfo' => new external_multiple_structure(
                new external_single_structure([
                    'role' => new external_value(PARAM_ALPHA, 'Video role name'),
                    'id' => new external_value(PARAM_TEXT, 'Jitsi participant id'),
                    'name' => new external_value(PARAM_TEXT, 'User display name'),
                    'pictureurl' => new external_value(PARAM_URL, 'User picture'),
                ])
            ),
        ]);
    }

    /**
     * Get speaker infomation
     *
     * @param plenum $plenum
     * @param motion|null $pending
     * @return array
     */
    protected static function userinfo(plenum $plenum, ?motion $pending): array {
        global $DB, $OUTPUT, $PAGE;

        $groupid = empty($pending) ? 0 : $pending->get('groupid');
        $records = $DB->get_records_sql(
            "SELECT s.*
               FROM {plenumform_jitsi2_speaker} s
               JOIN {plenum_motion} m ON m.id = s.motion
              WHERE m.groupid = :groupid
                    AND s.plenum = :plenum
                    AND s.status = :status",
            [
            'plenum' => $plenum->get_id(),
            'groupid' => $groupid,
            'status' => 0,
            ]
        );

        $speakers = [];

        foreach ($records as $record) {
            $user = core_user::get_user($record->usermodified);
            $userpicture = new user_picture($user);
            $speakers[(string)$record->role] = [
                'role' => $record->role ? 'chair' : 'speaker',
                'id' => $record->jitsiuserid,
                'name' => fullname($user),
                'pictureurl' => $userpicture->get_url($PAGE, $OUTPUT)->out(),
            ];
        }
        if (empty($speakers['1'])) {
            $speakers['1'] = [
                'role' => 'chair',
                'id' => '',
                'name' => get_string('chair', 'plenumform_deft'),
                'pictureurl' => $OUTPUT->image_url('chair-solid', 'plenumform_deft')->out(),
            ];
        }

        if (empty($speakers['0'])) {
            if ($pending) {
                $user = core_user::get_user($pending->get('usercreated'));
                $userpicture = new user_picture($user);
                $speakers['0'] = [
                    'role' => 'speaker',
                    'id' => '',
                    'name' => fullname($user),
                    'pictureurl' => $userpicture->get_url($PAGE, $OUTPUT)->out(),
                ];
            } else {
                $speakers['0'] = [
                    'role' => 'speaker',
                    'id' => '',
                    'name' => get_string('floor', 'plenumform_deft'),
                    'pictureurl' => $OUTPUT->image_url('microphone-solid', 'plenumform_deft')->out(),
                ];
            }
        }

        return array_values($speakers);
    }
}
