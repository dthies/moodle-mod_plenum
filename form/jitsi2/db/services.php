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
 * File description.
 *
 * @package   plenumform_jitsi2
 * @copyright 2024 Daniel Thies <dethies@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [

    'plenumform_jitsi2_join_room' => [
        'classname' => '\\plenumform_jitsi2\\external\\join_room',
        'methodname' => 'execute',
        'description' => 'Register entry in Jisti room',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'plenumform_jitsi2_publish_feed' => [
        'classname' => '\\plenumform_jitsi2\\external\\publish_feed',
        'methodname' => 'execute',
        'description' => 'Make feed available for a speaker',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'plenumform_jitsi2_raise_hand' => [
        'classname' => '\\plenumform_jitsi2\\external\\raise_hand',
        'methodname' => 'execute',
        'description' => 'Record hand raise change',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'plenumform_jitsi2_update_content' => [
        'classname' => '\\plenumform_jitsi2\\external\\update_content',
        'methodname' => 'execute',
        'description' => 'Get updated meeting information',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
