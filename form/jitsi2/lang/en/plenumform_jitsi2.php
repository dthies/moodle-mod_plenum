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
 * @package     plenumform_jitsi2
 * @category    string
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['appid'] = 'App id';
$string['appid_desc'] = 'This Jitsi server should be secured so only participants authorized by the activity can participate. The server administrator should set an App ID and secret to use here. See <a href="https://jitsi.github.io/handbook/docs/devops-guide/devops-guide-quickstart/">Jitsi documentation</a> to self host.';
$string['connectionconfiguration'] = 'Connection configuration';
$string['delay'] = 'Delay';
$string['delay_desc'] = 'Time to wait before updating content in seconds. Increase to reduce server load. Decrease to make more responsive.';
$string['disconnected'] = 'Disconnected';
$string['disconnectedmessage'] = 'You have left the room. Reload to reconnect.';
$string['eventhandraiseupdated'] = 'Hand raise updated';
$string['eventvideoended'] = 'Video ended';
$string['eventvideostarted'] = 'Video started';
$string['joinroom'] = 'Join room';
$string['pluginhelp'] = 'The Managed Jitsi video form plugin provides an integration using a Jitsi Meet server. Meeting participants are restricted to speaking only when they have the floor. It is for larger meetings where the chair needs more help maintaining order. However it may not be appropriate for heavy use or high security situations.';
$string['pluginname'] = 'Managed Jitsi video';
$string['privacy:connections'] = 'Speaking with Jitsi';
$string['privacy:metadata'] = 'The Managed Jitsi video meeting plugin does not store any personal data although Jitsi server may be used to record.';
$string['privacy:metadata:plenumform_jitsi2_speaker'] = 'Temporary data for state of user\'s video room connection in a meeting';
$string['privacy:metadata:plenumform_jitsi2_speaker:motion'] = 'The pendingmotion';
$string['privacy:metadata:plenumform_jitsi2_speaker:role'] = 'Role of speaker';
$string['privacy:metadata:plenumform_jitsi2_speaker:status'] = 'Whether finished';
$string['privacy:metadata:plenumform_jitsi2_speaker:timecreated'] = 'Time created';
$string['privacy:metadata:plenumform_jitsi2_speaker:timemodified'] = 'Time modified';
$string['privacy:metadata:plenumform_jitsi2_speaker:usermodified'] = 'User id for peer';
$string['room'] = 'Room';
$string['secret'] = 'Secret';
$string['secret_desc'] = 'This Jitsi server administrator should provide secret to access the server.';
$string['server'] = 'Server';
$string['server_desc'] = 'The Jitsi server to use for meetings';
