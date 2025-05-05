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
 * Plenary meeting main page js
 *
 * @package    mod_plenum
 * @module     mod_plenum/plenum
 * @copyright  2023 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Fragment from "core/fragment";
import Templates from "core/templates";
import ModalForm from "core_form/modalform";
import Notification from "core/notification";
import {get_string as getString} from "core/str";

const SELECTORS = {
    PREVIEW: '.modal-body [data-region="view-motion"]',
};

export default class Plenum {
    /**
     * Initialize player plugin
     *
     * @param {int} contextid
     *
     * @returns {bool}
     */
    constructor(contextid) {
        this.contextid = contextid;

        this.addListeners();

        return true;
    }

    /**
     * Register player events to respond to user interaction and play progress.
     */
    addListeners() {
        document.querySelector('body').removeEventListener('click', handleClick);
        document.querySelector('body').addEventListener('click', handleClick);
    }
}

/**
 * Handle click event
 *
 * @param {Event} e Event
 */
const handleClick = function(e) {
    const button = e.target.closest(
        '[data-region="plenum-motions"][data-contextid] [data-action],'
        + ' .modal-body [data-region="view-motion"] [data-action],'
        + ' .modal-body [data-region="plenum-activity-report"][data-contextid] [data-action]'
    );
    if (button) {
        const action = button.getAttribute('data-action'),
            contextid = button.closest('[data-contextid]').getAttribute('data-contextid');
        e.stopPropagation();
        e.preventDefault();

        if (action == 'move') {
            const type = button.getAttribute('data-type');
            const modalForm = new ModalForm({
                args: {
                    contextid: contextid,
                    type: button.getAttribute('data-type')
                },
                formClass: `plenumtype_${type}\\form\\edit_motion`,
                modalConfig: {title: getString('editingmotiontype', `plenumtype_${type}`)}
            });
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => {
                const motionCreated = new CustomEvent('motioncreated', {
                    detail: e.detail
                });
                document.body.dispatchEvent(motionCreated);
            });
            modalForm.show();
        } else if (['adopt', 'allow', 'decline', 'deny'].includes(action)) {
            const id = e.target.closest('[data-motion]').getAttribute('data-motion');
            const modalForm = new ModalForm({
                args: {
                    contextid: contextid,
                    id: id,
                    state: action
                },
                formClass: 'mod_plenum\\form\\change_state',
                modalConfig: {title: getString('confirm')}
            });
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => {
                const motionUpdated = new CustomEvent('motionupdated', {
                    detail: e.detail
                });
                document.body.dispatchEvent(motionUpdated);
            });
            modalForm.show();
        } else if (action == 'close') {
            const id = e.target.closest('[data-motion]').getAttribute('data-motion');
            const modalForm = new ModalForm({
                formClass: 'mod_plenum\\form\\close_motion',
                args: {contextid: contextid, id: id, state: action}
            });
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e) => {
                const motionUpdated = new CustomEvent('motionupdated', {
                    detail: e.detail
                });
                document.body.dispatchEvent(motionUpdated);
            });
            modalForm.show();
        } else if (action == 'preview') {
            const id = e.target.closest('[data-motion]').getAttribute('data-motion');
            Fragment.loadFragment(
                'mod_plenum',
                'motion',
                contextid,
                {id: id}
            ).done((html) => {
                if (button.closest(SELECTORS.PREVIEW)) {
                    Templates.replaceNodeContents(button.closest(SELECTORS.PREVIEW), html);
                } else {
                    Notification.alert(getString('viewmotion', 'mod_plenum'), html);
                }
            }).fail(Notification.exeption);
        }
    }
};
