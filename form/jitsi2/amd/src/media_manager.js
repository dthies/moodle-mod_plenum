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
 * @package    plenumform_jitsi2
 * @module     plenumform_jitsi2/media_manager
 * @copyright  2025 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
var domain;

import Ajax from "core/ajax";
import {get_string as getString} from "core/str";
import JitsiMeetJS from "plenumform_jitsi2/lib-jitsi-meet.min";
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
        this.userinfo = [];
        this.displayedTracks = [];
        this.videoTracks = {};
        this.audioTracks = {};

        if (delay) {
            setInterval(() => {
                this.updateMotions(contextid);
            }, delay);
        }

        JitsiMeetJS.init();
        JitsiMeetJS.setLogLevel(JitsiMeetJS.logLevels.DEBUG);

        this.connection = new JitsiMeetJS.JitsiConnection(null, jwt, {
            serviceUrl: `https://${ domain }/http-bind`,
            hosts: {
                domain: domain,
                muc: `conference.${ domain }`
            }
        });
        this.connection.addEventListener(JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED, () => {
            this.room = this.connection.initJitsiConference(room, {});
            this.room.addEventListener(JitsiMeetJS.events.conference.TRACK_ADDED, track => {
                this.onRemoteTrack(track);
            });
            this.room.addEventListener(JitsiMeetJS.events.conference.TRACK_REMOVED, track => {
                track.dispose();
            });
            this.room.addCommandListener('updatecontent', () => {
                this.updateMotions(contextid);
            });
            this.room.on(JitsiMeetJS.events.conference.CONFERENCE_JOINED, () => {
                this.updateMotions(contextid);
            });

            document.body.addEventListener(
                'motioncreated',
                () => {
                    this.room.sendCommandOnce('updatecontent', {
                        value: 'updatecontent',
                        attributes: {},
                        children: []
                    });
                }
            );
            document.body.addEventListener(
                'motionupdated',
                () => {
                    this.room.sendCommandOnce('updatecontent', {
                        value: 'updatecontent',
                        attributes: {},
                        children: []
                    });
                }
            );

            this.room.join();
        });
        this.connection.addEventListener(JitsiMeetJS.events.connection.CONNECTION_DISCONNECTED, () => {
            Notification.alert(
                getString('disconnected', 'plenumform_jitsi2'),
                getString('disconnectedmessage', 'plenumform_jitsi2')
            );
        });

        this.connection.connect();

        document.addEventListener('click', e => {
            this.handleClick(e);
        });
        document.body.addEventListener('click', e => {
            this.muteAudio(e);
        });

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
                methodname: 'plenumform_jitsi2_update_content'
            }])[0];
            if (response.motions) {
                Templates.replaceNodeContents(content, response.motions, response.javascript);
                this.userinfo = response.userinfo;
                this.updateMedia();
            }
            if (response.controls) {
                const selector = `[data-contextid="${contextid}"][data-region="plenum-deft-controls"]`;
                Templates.replaceNodeContents(selector, response.controls, '');
            }
            if (!response.sharevideo) {
                this.room.getLocalTracks().forEach(track => {
                    track.dispose();
                });
            }
        }
    }

    /**
     * Attach or detach media
     */
    updateMedia() {
        this.displayedTracks.forEach(async track => {
            if (!this.userinfo.find(speaker => speaker.id == track.getParticipantId())) {
                document.querySelectorAll(
                    `[data-region="slot-${ track.role }"] ${ track.getType() }`
                ).forEach(player => {
                    track.detach(player);
                });
                delete this.displayedTracks[this.displayedTracks.indexOf(track)];
            }
        });
        this.userinfo.forEach(speaker => {
            if (this.videoTracks[speaker.id]) {
                const track = this.videoTracks[speaker.id];
                if (!this.displayedTracks.includes(track)) {
                    track.role = speaker.role;
                    track.attach(document.querySelector(`[data-region="slot-${ speaker.role }"] ${ track.getType() }`));
                    this.displayedTracks.push(track);
                }
            }
            if (this.audioTracks[speaker.id]) {
                const track = this.audioTracks[speaker.id];
                if (!this.displayedTracks.includes(track)) {
                    track.role = speaker.role;
                    track.attach(document.querySelector(`[data-region="slot-${ speaker.role }"] ${ track.getType() }`));
                    this.displayedTracks.push(track);
                }
            }
            document.querySelectorAll(`[data-region="slot-${ speaker.role }"] .card-header`).forEach(function(h) {
                h.innerHTML = speaker.name;
            });
            document.querySelectorAll(`[data-region="slot-${ speaker.role }"] video`).forEach(function(video) {
                video.poster = speaker.pictureurl;
            });
        });
    }

    /**
     * Process new remote track
     *
     * @param {JitsiTrack} track New track
     */
    onRemoteTrack(track) {
        if (track.getType() == 'video') {
            this.videoTracks[track.getParticipantId()] = track;
        } else {
            this.audioTracks[track.getParticipantId()] = track;
        }
        this.updateMedia();
    }

    /**
     * Change published media in activity
     *
     * @param {bool} publish Whether to add or remove media
     */
    async publish(publish) {
        await Ajax.call([{
            args: {
                contextid: this.contextid,
                id: this.room.myUserId(),
                publish: publish
            },
            contextid: this.contextid,
            fail: Notification.exception,
            methodname: 'plenumform_jitsi2_publish_feed'
        }])[0];

        this.room.sendCommandOnce('updatecontent', {
            value: 'updatecontent',
            attributes: {},
            children: []
        });
    }

    /**
     * Handle button click
     *
     * @param {Event} e Click event
     */
    async handleClick(e) {
        const button = e.target.closest('button[data-action="publish"], button[data-action="unpublish"]');

        if (!button) {
            return;
        }

        this.room.getLocalTracks().forEach(track => {
            track.dispose();
        });

        if (button.dataset.action == 'publish') {
            const tracks = await JitsiMeetJS.createLocalTracks({
                devices: ['video', 'audio'],
                constraints: {aspectRatio: {exact: 1}, height: {ideal: 360}, width: {ideal: 360}}
            });
            tracks.forEach(track => {
                if (this[`${ track.getType() }Track`]) {
                    this.room.replaceTrack(this[`${ track.getType() }Track`], track);
                } else {
                    this.room.addTrack(track);
                }
                this[`${ track.getType() }Track`] = track;
            });
            this.publish(true);
        } else {
            this.publish(false);
        }
    }

    /**
     * Handle mute switch change
     *
     * @param {Event} e The switch event
     */
    muteAudio(e) {
        const input = e.target.closest('[data-region="audio-control"] input');
        if (!input) {
            return;
        }
        setTimeout(() => {
            if (input.checked) {
                document.querySelectorAll('[data-region="plenum-deft-media"] audio').forEach(audio => {
                    if (
                        !this.room.getLocalTracks().length
                        || !this.userinfo.find(speaker => (
                            (speaker.id == this.room.myUserId())
                            && audio.closest(`[data-region="slot-${ speaker.role }"]`)
                        ))
                    ) {
                        audio.volume = 1;
                        audio.muted = '';
                        audio.setAttribute('data-active', 'true');
                    } else {
                        audio.volume = 0;
                        audio.muted = true;
                    }
                    audio.play();
                });
            } else {
                document.querySelectorAll('audio').forEach(audio => {
                    audio.muted = true;
                    audio.removeAttribute('data-active');
                });
            }
        });
    }
}
