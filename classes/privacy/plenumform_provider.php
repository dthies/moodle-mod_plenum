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
 * This file contains the plenumform_provider interface.
 *
 * Plenum form sub plugins should implement this if they store personal information.
 *
 * @package mod_plenum
 * @copyright 2025 Daniel Thies <dethies@gmail.com>
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_plenum\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;

/**
 * Interface for plenum form subplugin privacy provider
 *
 * @package mod_plenum
 * @copyright 2025 Daniel Thies <dethies@gmail.com>
 */
interface plenumform_provider extends \core_privacy\local\request\plugin\subplugin_provider {
    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param course_module $cm Course module
     * @param context_module context
     * @param stdClass $user User record
     */
    public static function export_form_user_data($cm, $context, $user);

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist);

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function delete_data_for_users(approved_userlist $userlist);
}
