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

/**
 * Plugin administration pages are defined here.
 *
 * @package     plenumform_basic
 * @category    admin
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Add plugin settings.
    $setting = new admin_setting_heading(
        'plenumform_basic/description',
        new lang_string('description'),
        html_writer::div(new lang_string('pluginhelp', 'plenumform_basic'), 'alert alert-info')
    );
    $settings->add($setting);

    $name = new lang_string('delay', 'plenumform_basic');
    $description = new lang_string('delay_desc', 'plenumform_basic');
    $setting = new admin_setting_configtext(
        'plenumform_basic/delay',
        $name,
        $description,
        3,
        PARAM_FLOAT
    );
    $settings->add($setting);
}
