// This file is part of Moodle - http://moodle.org/ //
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

/*
 * Plenary meeting Jitsi integration media manager
 *
 * @package    plenumform_jitsi
 * @module     plenumform_jitsi/media_manager
 * @copyright  2023 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
var api;
var domain;
var conferenceOptions;

import Ajax from "core/ajax";
import JitsiMeetExternalAPI from "plenumform_jitsi/external_api";
import Notification from "core/notification";
import Templates from "core/templates";

export default class MediaManager {
    /**
     * Initialize player plugin
     *
     * @param {int} contextid
     * @param {int} delay
     * @param {string} server Jitsi server to use
     * @param {object} options Room options
     *
     * @returns {bool}
     */
    constructor(contextid, delay, server, options) {
        this.contextid = contextid;
        options.parentNode = document.querySelector('#meet');
        options.contextid = contextid;
        conferenceOptions = options;
        domain = server;

        if (delay) {
            setInterval(() => {
                updateMotions(contextid);
            }, delay);
        }

        document.removeEventListener('click', handleClick);
        document.addEventListener('click', handleClick);

        document.body.addEventListener(
            'motioncreated',
            () => {
                updateMotions(this.contextid);
                if (api) {
                    api.executeCommand('sendEndpointTextMessage', '', 'text');
                }
            }
        );
        document.body.addEventListener(
            'motionupdated',
            () => {
                updateMotions(this.contextid);
                if (api) {
                    api.executeCommand('sendEndpointTextMessage', '', 'text');
                }
            }
        );

        return true;
    }
}

/**
 * Update motions
 *
 * @param {int} contextid
 */
const updateMotions = async(contextid) => {
    const selector = `[data-contextid="${contextid}"][data-region="plenum-motions"]`;
    const content = document.querySelector(selector);
    if (content) {
        const response = await Ajax.call([{
            args: {
                contextid: contextid
            },
            contextid: contextid,
            fail: Notification.exception,
            methodname: 'mod_plenum_update_content'
        }])[0];
        if (response.motions) {
            Templates.replaceNodeContents(content, response.motions, response.javascript);
        }
    }
};

/**
 * Register joining the room
 *
 * @return {Promise}
 */
const register = function() {

    return Ajax.call([{
        args: {
            contextid: Number(conferenceOptions.contextid),
            join: true
        },
        contextid: conferenceOptions.contextid,
        fail: Notification.exception,
        methodname: 'plenumform_jitsi_join_room'
    }])[0];
};

/**
 * Leave the room
 *
 * @return {Promise}
 */
const leave = function() {
    return Ajax.call([{
        args: {
            contextid: Number(conferenceOptions.contextid),
            join: false
        },
        contextid: conferenceOptions.contextid,
        fail: Notification.exception,
        methodname: 'plenumform_jitsi_join_room'
    }])[0];
};

/**
 * Handle hand raise
 *
 * @param {object} e Event data
 * @return {Promise}
 */
const raiseHand = (e) => {
    return Ajax.call([{
        args: {
            contextid: Number(conferenceOptions.contextid),
            raisehand: !!e.handRaised
        },
        contextid: conferenceOptions.contextid,
        fail: Notification.exception,
        methodname: 'plenumform_jitsi_raise_hand'
    }])[0];
};

/**
 * Handle button click
 *
 * @param {Event} e Click event
 */
const handleClick = function(e) {
    const button = e.target.closest('button[data-action="joinroom"]');

    if (button) {
        if (api) {
            api.dispose();
        }
        api = new JitsiMeetExternalAPI(domain, conferenceOptions);
        button.parentNode.classList.add('hidden');
        conferenceOptions.parentNode.classList.remove('hidden');
        api.addListener('readyToClose', () => {
            button.parentNode.classList.remove('hidden');
            conferenceOptions.parentNode.classList.add('hidden');
            api.dispose();
        });
        api.addListener('videoConferenceJoined', register);

        api.addListener('endpointTextMessageReceived', () => {
            updateMotions(conferenceOptions.contextid);
        });
        api.addListener('videoConferenceLeft', leave);
        api.addListener('raiseHandUpdated', raiseHand);
    }
};
