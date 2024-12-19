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
 * Plenary meeting Deft socket
 *
 * @package    plenumform_deft
 * @module     plenumform_deft/socket
 * @copyright  2023 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Log from "core/log";
import Notification from "core/notification";
import SocketBase from "block_deft/socket";

export default class Socket extends SocketBase {
    /**
     * Renew token
     *
     * @param {int} contextid Context id of block
     */
    renewToken(contextid) {
        Ajax.call([{
            methodname: 'plenumform_deft_renew_token',
            args: {contextid: contextid},
            done: (replacement) => {
                Log.debug('Reconnecting');
                this.connect(contextid, replacement.token);
            },
            fail: Notification.exception
        }]);
    }
}
