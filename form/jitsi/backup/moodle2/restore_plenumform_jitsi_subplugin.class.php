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
 * Define restore step for plenumform_jitsi subplugin
 *
 * restore subplugin class that provides the data
 * needed to restore one plenumform_jitsi subplugin.
 *
 * @package     plenumform_jitsi
 * @copyright   2024 Daniel Thies
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_plenumform_jitsi_subplugin extends restore_subplugin {
    /**
     * Define subplugin structure
     *
     */
    protected function define_plenum_subplugin_structure() {

        $paths = [];

        $elename = $this->get_namefor('');
        $elepath = $this->get_pathfor('/jitsi_settings');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes the plenumform_deft element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_plenumform_jitsi($data) {
        global $DB;

        $data = (array) $data + (array) get_config('plenumform_jitsi');
        $data = (object)$data;
        $data->plenum = $this->get_new_parentid('plenum');
        $DB->insert_record('plenumform_jitsi', $data);
    }
}
