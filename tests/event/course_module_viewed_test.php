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

namespace mod_plenum\event;

use advanced_testcase;
use context_course;
use context_module;

/**
 * Plenary meeting events test cases.
 *
 * @package    mod_plenum
 * @copyright  2024 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_plenum\event\course_module_viewed
 * @group      mod_plenum
 */
final class course_module_viewed_test extends advanced_testcase {
    /**
     * Test course_module_viewed event.
     */
    public function test_course_module_viewed(): void {
        // There is no proper API to call to trigger this event, so what we are
        // doing here is simply making sure that the events returns the right information.

        $this->resetAfterTest();

        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('plenum', ['course' => $course->id]);

        $params = [
            'context' => context_module::instance($activity->cmid),
            'objectid' => $activity->id,
        ];
        $event = course_module_viewed::create($params);

        // Triggering and capturing the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_plenum\event\course_module_viewed', $event);
        $this->assertEquals(context_module::instance($activity->cmid), $event->get_context());
        $this->assertEquals($activity->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }
}
