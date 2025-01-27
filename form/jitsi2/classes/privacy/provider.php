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
 * Privacy Subsystem implementation for plenumform_jitsi2.
 *
 * @package    plenumform_jitsi2
 * @category   privacy
 * @copyright  2024 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plenumform_jitsi2\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for plenumform_jitsi2.
 *
 * @copyright  2024 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \mod_plenum\privacy\plenumform_provider, \core_privacy\local\metadata\provider {
    /**
     * Returns meta data about this system.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'plenumform_jitsi2_speaker',
            [
                'motion' => 'privacy:metadata:plenumform_jitsi2_speaker:motion',
                'role' => 'privacy:metadata:plenumform_jitsi2_speaker:role',
                'status' => 'privacy:metadata:plenumform_jitsi2_speaker:status',
                'timecreated' => 'privacy:metadata:plenumform_jitsi2_speaker:timecreated',
                'timemodified' => 'privacy:metadata:plenumform_jitsi2_speaker:timemodified',
                'usermodified' => 'privacy:metadata:plenumform_jitsi2_speaker:usermodified',
            ],
            'privacy:metadata:plenumform_jitsi2_speaker'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {plenumform_jitsi2_speaker} p ON p.plenum = cm.instance
                 WHERE c.contextlevel = :contextlevel
                   AND p.usermodified = :userid";
        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $sql = "SELECT p.usermodified
                  FROM {plenumform_jitsi2_speaker} p
                  JOIN {course_modules} cm ON p.plenum = cm.instance
                  JOIN {context} c ON cm.id = c.instanceid
                 WHERE c.contextlevel = :contextlevel
                   AND c.id = :contextid";
        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'contextid' => $context->id,
        ];

        $userlist->add_from_sql('usermodified', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_form_user_data($cm, $context, $user) {
        global $DB;

        $speakers = $DB->get_records('plenumform_jitsi2_speaker', [
            'plenum' => $cm->instance,
            'usermodified' => $user->id,
        ]);

        $speakerdata = [];
        foreach ($speakers as $speaker) {
            $speakerdata['cmid'] = $cm->id;
            $speakerdata['connections'][] = (object)[
                'motion' => $speaker->motion,
                'role' => $speaker->role,
                'status' => $speaker->status,
                'timecreated' => transform::datetime($speaker->timecreated),
                'timemodified' => transform::datetime($speaker->timemodified),
                'usermodified' => $speaker->usermodified,
            ];
        }

        // Write the result.
        if (!empty($speakerdata)) {
            self::export_speaker_data_for_user($speakerdata, $context, $user);
        }
    }

    /**
     * Export the supplied personal data for a single plenary meeting activity, along with any generic data or area files.
     *
     * @param array $speakerdata the personal data to export for the meeting.
     * @param \context_module $context the context of the meeting.
     * @param \stdClass $user the user record
     */
    protected static function export_speaker_data_for_user(array $speakerdata, \context_module $context, \stdClass $user) {
        // Fetch the generic module data for the plenary meeting.
        $contextdata = helper::get_context_data($context, $user);
        writer::with_context($context)->export_data([], $contextdata);

        // Merge with speaker data and write it.
        $contextdata = (object)array_merge((array)$contextdata, $speakerdata);
        writer::with_context($context)->export_data([
            get_string('privacy:connections', 'plenumform_jitsi2'),
        ], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        \core_comment\privacy\provider::delete_comments_for_users($userlist, 'plenumform_jitsi2');

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $userids = $userlist->get_userids();
        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'params', true, true);

        $cm = get_coursemodule_from_id('plenum', $context->instanceid, MUST_EXIST);

        $DB->delete_records(
            'plenumform_jitsi2_speaker',
            "usermodified $usersql AND plenum = :instanceid",
            [
                'instanceid' => $cm->instance,
            ] + $userparams
        );
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        $instanceids = [];
        foreach ($contextlist->get_contexts() as $context) {
            if (
                $context instanceof \context_module
                && $cm = get_coursemodule_from_id('plenum', $context->instanceid)
            ) {
                $instanceids[] = $cm->instance;
            }
        }
        if (empty($instanceids)) {
            return;
        }
        [$sql, $params] = $DB->get_in_or_equal($instanceids, SQL_PARAMS_NAMED);

        $DB->delete_records_select(
            'plenumform_jitsi2_speaker',
            "plenum $sql AND usermodified = :usermodified",
            ['usermodified' => $userid] + $params
        );
    }
}
