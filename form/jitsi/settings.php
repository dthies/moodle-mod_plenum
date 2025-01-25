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
 * @package     plenumform_jitsi
 * @category    admin
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Add plugin settings.
    $setting = new admin_setting_heading(
        'plenumform_jitsi/description',
        new lang_string('description'),
        html_writer::div(new lang_string('pluginhelp', 'plenumform_jitsi'), 'alert alert-info')
    );
    $settings->add($setting);

    $setting = new admin_setting_heading(
        'plenumform_jitsi/connetionconfig',
        new lang_string('connectionconfiguration', 'plenumform_jitsi'),
        ''
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi/delay',
        new lang_string('delay', 'plenumform_jitsi'),
        new lang_string('delay_desc', 'plenumform_jitsi'),
        3,
        PARAM_FLOAT
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi/server',
        new lang_string('server', 'plenumform_jitsi'),
        new lang_string('server_desc', 'plenumform_jitsi'),
        'meet.jit.si',
        PARAM_HOST
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi/appid',
        new lang_string('appid', 'plenumform_jitsi'),
        new lang_string('appid_desc', 'plenumform_jitsi'),
        '',
        PARAM_HOST
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'plenumform_jitsi/secret',
        new lang_string('secret', 'plenumform_jitsi'),
        new lang_string('secret_desc', 'plenumform_jitsi'),
        '',
        PARAM_HOST
    );
    $settings->add($setting);

    $setting = new admin_setting_heading(
        'plenumform_jitsi/uiconfig',
        new lang_string('uiconfiguration', 'plenumform_jitsi'),
        ''
    );
    $settings->add($setting);

    $options = [
        'camera',
        'chat',
        'fullscreen',
        'hangup',
        'microphone',
        'participants-pane',
        'raisehand',
        'select-background',
        'settings',
        'tileview',
        'videoquality',
    ];
    $setting = new admin_setting_configmultiselect(
        'plenumform_jitsi/toolbar',
        new lang_string('toolbar', 'plenumform_jitsi'),
        new lang_string('toolbar_desc', 'plenumform_jitsi'),
        ['camera', 'hangup', 'microphone', 'raisehand'],
        array_combine($options, $options)
    );
    $settings->add($setting);
}
