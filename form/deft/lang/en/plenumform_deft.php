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
 * @package     plenumform_deft
 * @category    string
 * @copyright   2023 Daniel Thies <dethies@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activationrequired'] = 'The <em>Deft response</em> block is configured, but the service is not activated. <a href="{$a}">Deft response settings</a>';
$string['chair'] = 'Chair';
$string['configurationrequired'] = 'Configuration required';
$string['configurationstatus'] = 'Configuration status';
$string['configurationstatus_desc'] = 'The Deft response block needs to be configured with the service from deftly.us for this meeting form to work correctly. The status of this connection is evaluated and displayed here.';
$string['configurationwarning'] = 'The Deft meeting form is not correctly configured to work with Deft response block. Please ask administrator to check configuration.';
$string['configuredeftblock'] = 'The <em>Deft response</em> block needs to be configured with "Enable updating" and "Enable video" enabled, and the <a href="https://deftly.us">deftly.us</a> service needs to be activated. <a href="{$a}">Deft response settings</a>.';
$string['deft:sharevideo'] = 'Share video';
$string['deft:viewvideo'] = 'View video';
$string['disableaudio'] = 'Disable audio';
$string['enableaudio'] = 'Enable audio';
$string['enableaudio_help'] = 'Click this to toggle sound in this meeting on or off.';
$string['eventvideoended'] = 'Video ended';
$string['eventvideostarted'] = 'Video started';
$string['floor'] = 'Floor speaker';
$string['pluginhelp'] = 'The Deft meeting form uses externally hosted servers at deftly.us. This integration is provided by the developer to support robust large scale events. You can connect by configuring the Deft response plugin for the purpose of evaluation, testing and light use, but no warranty of service is given for free access. All software and service given for free is AS IS. If you are interested commercial service, please create a support request at defly.us.';
$string['pluginname'] = 'Deft video';
$string['privacy:connections'] = 'Speaking with Deft';
$string['privacy:metadata:plenumform_deft_peer'] = 'Temporary data for state of user\'s video room connection in a meeting';
$string['privacy:metadata:plenumform_deft_peer:motion'] = 'The pending motion';
$string['privacy:metadata:plenumform_deft_peer:mute'] = 'Whether mute';
$string['privacy:metadata:plenumform_deft_peer:status'] = 'Whether finished';
$string['privacy:metadata:plenumform_deft_peer:timecreated'] = 'Time created';
$string['privacy:metadata:plenumform_deft_peer:timemodified'] = 'Time modified';
$string['privacy:metadata:plenumform_deft_peer:type'] = 'Type of connection';
$string['privacy:metadata:plenumform_deft_peer:usermodified'] = 'User id for peer';
$string['privacy:metadata:plenumform_deft_peer:uuid'] = 'Mobile device id';
$string['sharevideo'] = 'Share video';
$string['stopvideo'] = 'Stop video sharing';
$string['videodisabled'] = 'You are not able to take the floor unless recognized by the chair.';
$string['videoserviceconfigured'] = 'The <em>Deft response</em> block is configured and service is activated. <a href="{$a}">Test connection</a>';
