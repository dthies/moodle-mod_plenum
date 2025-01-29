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

use cache;
use context;
use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_plenum\motion;
use mod_plenum\plugininfo\plenum;
use stdClass;

/**
 * External function to offer feed to venue
 *
 * @package    plenumform_jitsi2
 * @copyright  2023 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publish_feed extends external_api {
    /**
     * Get parameter definition
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Context id for the meeting'),
                'id' => new external_value(PARAM_TEXT, 'Jitsi user id for session'),
                'publish' => new external_value(PARAM_BOOL, 'Whether to publish or not', VALUE_DEFAULT, true),
            ]
        );
    }

    /**
     * Publish feed
     *
     * @param int $contextid Module context id
     * @param string $id Jitsi user id
     * @param bool $publish Whether to publish
     * @return array
     */
    public static function execute($contextid, $id, $publish): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'id' => $id,
            'publish' => $publish,
        ]);
        $context = context::instance_by_id($contextid);

        $plenum = \core\di::get(\mod_plenum\manager::class)->get_plenum($context);
        $cm = $plenum->get_course_module();
        $motion = \mod_plenum\motion::immediate_pending($context);

        require_capability('mod/plenum:meet', $context);
        if ($publish && $motion->get('usercreated') != $USER->id) {
            require_capability('mod/plenum:preside', $context);
        }
        self::validate_context($context);

        if (
            !$publish && ($peer = $DB->get_record_select(
                'plenumform_jitsi2_speaker',
                "usermodified = :userid AND status = 0",
                ['userid' => $USER->id]
            ))
        ) {
            $peer->timemodified = time();
            $peer->status = 1;
            $DB->update_record('plenumform_jitsi2_speaker', $peer);
        } else if ($publish) {
            $timenow = time();
            $role = has_capability('mod/plenum:preside', $context) ? 1 : 0;
            $records = $DB->get_records_sql(
                "SELECT s.* FROM {plenumform_jitsi2_speaker} s
                   JOIN {plenum_motion} m ON m.id = s.motion
                  WHERE m.groupid = groupid
                        AND s.status = :status
                        AND s.plenum = :plenum",
                [
                    'role' => $role,
                    'groupid' => $motion->get('groupid'),
                    'plenum' => $cm->instance,
                    'status' => 0,
                ]
            );
            foreach ($records as $record) {
                $record->timemodified = time();
                $record->status = 1;
                $DB->update_record('plenumform_jitsi2_speaker', $record);
            }

            $records = $DB->get_records('plenumform_jitsi2_speaker', [
                'usermodified' => $USER->id,
                'status' => 0,
            ]);
            foreach ($records as $record) {
                $record->timemodified = $timenow;
                $record->status = 1;
                $DB->update_record('plenumform_jitsi2_speaker', $record);
            }

            $speakerid = $DB->insert_record('plenumform_jitsi2_speaker', [
                'plenum' => $cm->instance,
                'jitsiuserid' => $id,
                'usermodified' => $USER->id,
                'timecreated' => $timenow,
                'timemodified' => $timenow,
                'motion' => $motion->get('id'),
                'status' => 0,
                'role' => $role,
            ]);
        } else {
            return [
                'status' => false,
            ];
        }

        $params = [
            'context' => $context,
            'objectid' => $cm->instance,
            'other' => [
                'motion' => $peer->motion,
            ],
        ];

        if ($publish) {
            $event = \plenumform_jitsi2\event\video_started::create($params);
        } else {
            $event = \plenumform_jitsi2\event\video_ended::create($params);
        }
        $event->trigger();

        return [
            'status' => true,
        ];
    }

    /**
     * Get return definition
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether changed'),
        ]);
    }
}
