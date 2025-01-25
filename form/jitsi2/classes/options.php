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

namespace plenumform_jitsi2;

use cache;
use cm_info;
use context_module;
use moodleform;
use MoodleQuickForm;
use moodle_url;
use stdClass;
use mod_plenum\hook\after_motion_updated;
use mod_plenum\motion;
use mod_plenum\options_base;
use plenumtype_open\hook\after_data;

/**
 * Class handling options Plenary meeting plugin
 *
 * @package     plenumform_jitsi2
 * @copyright   2024 Daniel Thies <dethies@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options extends options_base {
    /**
     * Table to save options
     */
    protected const TABLE = 'plenumform_jitsi2';

    /**
     * Called mform mod_form after_data to add form specific options
     *
     * @param MoodleQuickForm $mform Form to which to add fields
     */
    public static function create_settings_elements(MoodleQuickForm $mform) {
    }

    /**
     * Modify open motion form
     *
     * @param after_data $hook Hook for open motion definition
     */
    public static function form_elements(after_data $hook) {
    }

    /**
     * Prepares the form before data are set
     *
     * @param  array $defaultvalues
     * @param  int $instance
     */
    public static function data_preprocessing(array &$defaultvalues, int $instance) {
        global $DB;

        if (!empty($instance)) {
            $defaultvalues['room'] = $DB->get_field('plenumform_jitsi2', 'room', ['plenum' => $instance]);
        }
    }

    /**
     * Update content after motion modified
     *
     * @param after_motion_updated $hook Hook
     */
    public static function after_motion_updated(\mod_plenum\hook\after_motion_updated $hook) {
        global $DB;

        if (
            empty($DB->get_record('plenum', [
                'id' => $hook->get_coursemodule()->instance,
                'form' => 'jitsi2',
            ]))
        ) {
            return;
        }

        $cm = $hook->get_coursemodule();

        if ($pending = motion::immediate_pending($hook->get_context())) {
            $speakers  = $DB->get_records_select(
                'plenumform_jitsi2_speaker',
                'status = 0 AND role = 0 AND NOT usermodified = ?',
                [$pending->get('usercreated')]
            );
        } else {
            $speakers = [];
        }

        foreach ($speakers as $speaker) {
            $speaker->timemodified = time();
            $speaker->status = 1;
            $DB->update_record('plenumform_jitsi2_speaker', $speaker);
        }
    }
}
