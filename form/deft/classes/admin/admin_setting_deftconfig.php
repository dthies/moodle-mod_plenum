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

namespace plenumform_deft\admin;

use admin_setting;
use html_writer;
use html_table;
use lang_string;
use moodle_url;
use stdClass;

/**
 * Special class for setting up Deftly service
 *
 * @package plenumform_deft
 * @copyright Daniel Thies
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_deftconfig extends admin_setting {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct(
            'plenumformdeft',
            new lang_string('configurationstatus', 'plenumform_deft'),
            new lang_string('configurationstatus_desc', 'plenumform_deft'),
            ''
        );
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything
     *
     * @param string $data Unused
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $DB, $OUTPUT;

        $url = new moodle_url('/admin/settings.php', [
            'section' => 'blocksettingdeft',
        ]);
        $testurl = new moodle_url("/blocks/deft/toolconfigure.php");

        $context = [
            'status' => new lang_string('configurationrequired', 'plenumform_deft'),
            'isinfo' => true,
        ];

        if (!get_config('block_deft', 'enableupdating')) {
            $context['message'] = new lang_string('configuredeftblock', 'plenumform_deft', $url->out());
        } else if (!get_config('block_deft', 'enablevideo')) {
            $context['message'] = new lang_string('configuredeftblock', 'plenumform_deft', $url->out());
        } else if (
            !$DB->get_field('lti_types', 'clientid', ['tooldomain' => 'deftly.us'])
        ) {
            $context['message'] = new lang_string('activationrequired', 'plenumform_deft', $url->out());
        } else {
            $context['message'] = new lang_string('videoserviceconfigured', 'plenumform_deft', $testurl->out());
            $context['isok'] = true;
            $context['isinfo'] = false;
            $context['status'] = new lang_string('ok');
        }

        $return = $OUTPUT->render_from_template('plenumform_deft/configuration', $context);

        $defaultinfo = null;
        return format_admin_setting($this, $this->visiblename, $return, $this->description, true, '', $defaultinfo, $query);
    }
}
