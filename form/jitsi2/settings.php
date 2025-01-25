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
 * @package     plenumform_jitsi2
 * @category    admin
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Add plugin settings.
    $setting = new admin_setting_heading(
        'plenumform_jitsi2/description',
        new lang_string('description'),
        html_writer::div(new lang_string('pluginhelp', 'plenumform_jitsi2'), 'alert alert-info')
    );
    $settings->add($setting);

    $setting = new admin_setting_heading(
        'plenumform_jitsi2/connetionconfig',
        new lang_string('connectionconfiguration', 'plenumform_jitsi2'),
        ''
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi2/delay',
        new lang_string('delay', 'plenumform_jitsi2'),
        new lang_string('delay_desc', 'plenumform_jitsi2'),
        3,
        PARAM_FLOAT
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi2/server',
        new lang_string('server', 'plenumform_jitsi2'),
        new lang_string('server_desc', 'plenumform_jitsi2'),
        '',
        PARAM_HOST
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi2/appid',
        new lang_string('appid', 'plenumform_jitsi2'),
        new lang_string('appid_desc', 'plenumform_jitsi2'),
        '',
        PARAM_HOST
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi2/secret',
        new lang_string('secret', 'plenumform_jitsi2'),
        new lang_string('secret_desc', 'plenumform_jitsi2'),
        '',
        PARAM_HOST
    );
    $settings->add($setting);
}
