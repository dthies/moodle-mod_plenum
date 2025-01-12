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
var options = {
    configOverwrite: {
        startWithAudioMuted: true
    },
    width: 640,
    height: 480,
    parentNode: document.querySelector('#meet')
};
var domain;

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
     * @param {string} room Room name
     * @param {object} userinfo User information to pass to meeting
     * @param {string} jwt JWT authentication token
     *
     * @returns {bool}
     */
    constructor(contextid, delay, server, room, userinfo, jwt) {
        this.contextid = contextid;
        domain = server;
        options.userInfo = userinfo;
        options.roomName = room;
        options.contextid = contextid;
        if (jwt) {
            options.jwt = jwt;
        }

        if (delay) {
            setInterval(() => {
                this.updateMotions(contextid);
            }, delay);
        }

        document.addEventListener('click', handleClick);

        return true;
    }

    /**
     * Update motions
     *
     * @param {int} contextid
     */
    async updateMotions(contextid) {
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
    }
}

/**
 * Register joining the room
 *
 * @return {Promise}
 */
const register = () => {
    return Ajax.call([{
        args: {
            contextid: Number(options.contextid),
            join: true
        },
        contextid: options.contextid,
        fail: Notification.exception,
        methodname: 'plenumform_jitsi_join_room'
    }])[0];
};

/**
 * Leave the room
 *
 * @return {Promise}
 */
const leave = () => {
    return Ajax.call([{
        args: {
            contextid: Number(options.contextid),
            join: false
        },
        contextid: options.contextid,
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
            contextid: Number(options.contextid),
            raisehand: !!e.handRaised
        },
        contextid: options.contextid,
        fail: Notification.exception,
        methodname: 'plenumform_jitsi_raise_hand'
    }])[0];
};

/**
 * Handle button click
 *
 * @param {Event} e Click event
 */
const handleClick = e => {
    const button = e.target.closest('button[data-action="joinroom"]');

    if (button) {
        const api = new JitsiMeetExternalAPI(domain, options);
        button.classList.add('hidden');
        options.parentNode.classList.remove('hidden');
        api.addListener('readyToClose', () => {
            button.classList.remove('hidden');
            options.parentNode.classList.add('hidden');
            api.dispose();
        });
        api.addListener('videoConferenceJoined', register);
        api.addListener('videoConferenceLeft', leave);
        api.addListener('raiseHandUpdated', raiseHand);
    }
};
