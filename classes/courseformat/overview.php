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

namespace mod_plenum\courseformat;

use cm_info;
use core\url;
use mod_plenum\plenum;
use core\output\action_link;
use core\output\renderer_helper;
use core\output\local\properties\button;
use core\output\local\properties\text_align;
use core_courseformat\output\local\overview\overviewaction;
use core_courseformat\local\overview\overviewitem;

/**
 * Plenary meeting overview integration.
 *
 * @package    mod_plenum
 * @copyright  2025 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview extends \core_courseformat\activityoverviewbase {
    #[\Override]
    public function get_extra_overview_items(): array {
        return [
            'sessions' => $this->get_extra_sessions_overview(),
        ];
    }

    #[\Override]
    public function get_actions_overview(): ?overviewitem {
        if (!has_capability('mod/plenum:view', $this->context)) {
            return null;
        }

        $url = new url('/mod/plenum/view.php', ['id' => $this->cm->id]);
        $text = get_string('view');

        if (class_exists('overviewaction')) {
            $content = new overviewaction(
                url: $url,
                text: $text,
            );
        } else {
            $content = new action_link(
                url: $url,
                text: $text,
                attributes: ['class' => button::SECONDARY_OUTLINE->classes()],
            );
        }

        return new overviewitem(
            name: get_string('actions'),
            value: 1,
            content: $content,
            textalign: text_align::CENTER,
        );
    }

    /**
     * Get the overview of sessions
     *
     * @return overviewitem An overview item for the sesions
     */
    private function get_extra_sessions_overview(): overviewitem {
        if (
            has_capability('mod/plenum:preside', $this->cm->context)
            && $sessions = count(\mod_plenum\motion::get_records([
                'plenum' => $this->cm->instance,
                'type' => 'open',
            ]))
        ) {
            $sessions = \html_writer::link(
                new url('/mod/plenum/motion.php', ['cmid' => $this->cm->id]),
                $sessions
            );
        } else {
            $sessions = '';
        }

        return new overviewitem(
            name: get_string('sessions', 'mod_plenum'),
            value: $sessions,
            content: $sessions,
        );
    }
}
