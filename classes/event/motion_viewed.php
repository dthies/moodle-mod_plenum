<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_plenum\event;

use core\event\base;

/**
 * The motion_viewed event class.
 *
 * @package     mod_plenum
 * @category    event
 * @copyright   2023 Daniel Thies <dthies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class motion_viewed extends base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = 'plenum_motion';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('motion_viewed', 'mod_plenum');
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with the id '$this->userid' viewed a motion with id '$this->objectid'" .
                " for Plenary meeting activity with the course module id '$this->contextinstanceid'.";
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/plenum/motion.php', [
            'id' => $this->objectid,
        ]);
    }

    /**
     * This is used when restoring course logs where it is required that we
     * map the objectid to it's new value in the new course.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'plenum_motion', 'restore' => 'plenum_motion'];
    }
}
