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
 * Plugin strings are defined here.
 *
 * @package     plenumform_jitsi
 * @category    string
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['appid'] = 'App id';
$string['appid_desc'] = 'This Jitsi server should be secured so only participants authorized by the activity can participate. The server administrator should set an App ID and secret to use here. See <a href="https://jitsi.github.io/handbook/docs/devops-guide/devops-guide-quickstart/">Jitsi documentation</a> to self host.';
$string['connectionconfiguration'] = 'Connection configuration';
$string['delay'] = 'Delay';
$string['delay_desc'] = 'Set to non zero value to reload motions periodically even when Jitsi is not connected. Time to wait before updating content in seconds. Increase to reduce server load. Decrease to make more responsive.';
$string['eventhandraiseupdated'] = 'Hand raise updated';
$string['eventvideoended'] = 'Video ended';
$string['eventvideostarted'] = 'Video started';
$string['joinmessage'] = 'You are currently not connected with the media server. Press button below to join the meeting room.';
$string['joinroom'] = 'Join room';
$string['pluginhelp'] = 'The Jitsi video form plugin provides an integration with a Jitsi Meet server. A standard Jitsi Meet meeting room is embedded in the activity for participants to communicate. Access to the room is managed by to plugin to admit participants to the room. It may be appropriate to use for smaller meetings where participants are familiar with the meeting tools and can moderate the meeting manually.';
$string['pluginname'] = 'Jitsi video';
$string['privacy:metadata'] = 'The Jitsi video meeting plugin does not store any personal data although Jitsi server may be used to record.';
$string['room'] = 'Room';
$string['secret'] = 'Secret';
$string['secret_desc'] = 'This Jitsi server administrator should provide secret to access the server.';
$string['server'] = 'Server';
$string['server_desc'] = 'The Jitsi server to use for meetings';
$string['toolbar'] = 'Toolbar';
$string['toolbar_desc'] = 'Select the buttons to be displayed on the toolbar in a meeting. Hold CTL key to select multiple values.';
$string['uiconfiguration'] = 'User interface configuration';
