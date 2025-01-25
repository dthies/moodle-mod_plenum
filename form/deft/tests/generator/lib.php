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

use mod_plenum\motion;

/**
 * Plenary meeting module data generator class
 *
 * @package    plenumform_deft
 * @category   test
 * @copyright  2024 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plenumform_deft_generator extends testing_module_generator {
    /**
     * Create peer record
     *
     * @param motion $motion
     * @return motion
     */
    public function create_peer(motion $motion): \stdClass {
        global $DB, $USER, $SESSION;

        $record = new stdClass();

        $record->plenum = $motion->get('plenum');
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;
        $record->usercreated = $USER->id;
        $record->usermodified = $USER->id;
        $record->motion = $motion->get('id');
        $record->sessionid = 0;

        $motion = new motion(0, $record);

        $record->id = $DB->insert_record('plenumform_deft_peer', $record);

        return $record;
    }
}
