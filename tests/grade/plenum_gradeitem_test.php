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

namespace mod_plenum\grade;

use context_module;
use core_grades\component_gradeitem;
use mod_plenum\grades\plenum_gradeitem as gradeitem;
use gradingform_controller;
use mod_plenum\grades\plenum_gradeitem;
use mod_plenum\plenum;

/**
 * Tests for the Plenary meeting gradeitem.
 *
 * @package   mod_plenum
 * @copyright Daniel Thies <dethies@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \mod_plenum\grades\gradeitems
 * @covers    \mod_plenum\grades\plenum_gradeitem
 * @group      mod_plenum
 */
final class plenum_gradeitem_test extends \advanced_testcase {
    /**
     * Test fetching of a grade for a user when the grade has been created.
     */
    public function test_get_grade_for_user_exists(): void {
        $plenum = $this->get_plenum_instance([
            'grade' => 0,
        ]);
        $course = $plenum->get_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $grader = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $gradeitem = component_gradeitem::instance('mod_plenum', $plenum->get_context(), 'plenum');

        // Create the grade record.
        $grade = $gradeitem->create_empty_grade($student, $grader);

        $this->assertIsObject($grade);
        $this->assertEquals($student->id, $grade->userid);
    }

    /**
     * Test fetching of a grade for a user when the grade has been created.
     */
    public function test_user_has_grade(): void {
        $plenum = $this->get_plenum_instance([
            'grade' => 100,
        ]);
        $course = $plenum->get_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $grader = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $gradeitem = component_gradeitem::instance('mod_plenum', $plenum->get_context(), 'plenum');

        $hasgrade = $gradeitem->user_has_grade($student);
        $this->assertEquals(false, $hasgrade);
        // Create the grade record.
        $gradeitem->create_empty_grade($student, $grader);

        $hasgrade = $gradeitem->user_has_grade($student);
        $this->assertEquals(false, $hasgrade);

        // Store a new value.
        $gradeitem->store_grade_from_formdata($student, $grader, (object) ['grade' => 97]);
         $hasgrade = $gradeitem->user_has_grade($student);
         $this->assertEquals(true, $hasgrade);
    }

    /**
     * Ensure that it is possible to get, and update, a grade for a user when simple direct grading is in use.
     */
    public function test_get_and_store_grade_for_user_with_simple_direct_grade(): void {
        $plenum = $this->get_plenum_instance([
            'grade' => 100,
        ]);
        $course = $plenum->get_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $grader = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $gradeitem = component_gradeitem::instance('mod_plenum', $plenum->get_context(), 'plenum');

        // Create the grade record.
        $grade = $gradeitem->create_empty_grade($student, $grader);

        $this->assertIsObject($grade);
        $this->assertEquals($student->id, $grade->userid);

        // Store a new value.
        $gradeitem->store_grade_from_formdata($student, $grader, (object) ['grade' => 97]);
    }

    /**
     * Ensure that it is possible to get, and update, a grade for a user when a rubric is in use.
     */
    public function test_get_and_store_grade_for_user_with_rubric(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = \testing_util::get_data_generator();
        $gradinggenerator = $generator->get_plugin_generator('core_grading');
        $rubricgenerator = $generator->get_plugin_generator('gradingform_rubric');

        $plenum = $this->get_plenum_instance([
            'grade' => 100,
        ]);
        $course = $plenum->get_course();
        $context = $plenum->get_context();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $grader = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $editor = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        // Note: This must be run as a user because it messes with file uploads and drafts.
        $this->setUser($editor);

        $controller = $rubricgenerator->get_test_rubric($context, 'mod_plenum', 'plenum');

        // Create the plenum_gradeitem object that we'll be testing.
        $gradeitem = component_gradeitem::instance('mod_plenum', $plenum->get_context(), 'plenum');

        // The current grade should be null initially.
        $this->assertCount(0, $DB->get_records('plenum_grades'));
        $grade = $gradeitem->get_grade_for_user($student, $grader);
        $instance = $gradeitem->get_advanced_grading_instance($grader, $grade);

        $this->assertIsObject($grade);
        $this->assertEquals($student->id, $grade->userid);
        $this->assertEquals($plenum->get_id(), $grade->plenum);

        $rubricgenerator = $generator->get_plugin_generator('gradingform_rubric');
        $data = $rubricgenerator->get_submitted_form_data($controller, $grade->id, [
            'Spelling is important' => [
                'score' => 2,
                'remark' => 'Abracadabra',
            ],
            'Pictures' => [
                'score' => 1,
                'remark' => 'More than one picture',
            ],
        ]);

        // Store a new value.
        $gradeitem->store_grade_from_formdata($student, $grader, (object) [
            'instanceid' => $instance->get_id(),
            'advancedgrading' => $data,
        ]);
    }

    /**
     * Test the logic in the should_grade_only_active_users() method.
     *
     * @covers \mod_plenum\grades\plenum_gradeitem::should_grade_only_active_users
     * @dataProvider should_grade_only_active_users_provider
     * @param bool $showonlyactiveenrolconfig Whether the 'grade_report_showonlyactiveenrol' is enabled or not.
     * @param bool $showonlyactiveenrolpreference Whether the 'grade_report_showonlyactiveenrol' preference is enabled or not.
     * @param bool $viewsuspendeduserscapability Whether the 'moodle/course:viewsuspendedusers' capability is allowed or not.
     * @param bool $expected The expected result.
     */
    public function test_should_grade_only_active_users(
        bool $showonlyactiveenrolconfig,
        bool $showonlyactiveenrolpreference,
        bool $viewsuspendeduserscapability,
        bool $expected
    ): void {
        global $DB;

        $this->resetAfterTest();

        $plenum = $this->get_plenum_instance([
            'grade_plenum' => 100,
        ]);
        $course = $plenum->get_course();
        $gradeitem = component_gradeitem::instance('mod_plenum', $plenum->get_context(), 'plenum');

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        $editingteacherroleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);

        // Set the 'grade_report_showonlyactiveenrol' config setting.
        set_config('grade_report_showonlyactiveenrol', $showonlyactiveenrolconfig);

        $this->setUser($user);
        // Set the 'grade_report_showonlyactiveenrol' user preference.
        set_user_preference('grade_report_showonlyactiveenrol', $showonlyactiveenrolpreference);

        // Set the 'moodle/course:viewsuspendedusers' user capability.
        assign_capability('moodle/course:viewsuspendedusers', $viewsuspendeduserscapability ?
            CAP_ALLOW : CAP_PROHIBIT, $editingteacherroleid, \context_course::instance($course->id));

        $this->assertEquals($expected, $gradeitem->should_grade_only_active_users());
    }

    /**
     * Data provider for test_should_grade_only_active_users.
     *
     * @return array
     */
    public static function should_grade_only_active_users_provider(): array {
        return [
            'Enabled showonlyactiveenrol setting; enabled showonlyactiveenrol preference; view suspended users capability' =>
                [
                    'showonlyactiveenrolconfig' => true,
                    'showonlyactiveenrolpreference' => true,
                    'viewsuspendeduserscapability' => true,
                    'expected' => true,
                ],
            'Disabled showonlyactiveenrol setting; enabled showonlyactiveenrol preference; view suspended users capability' =>
                [
                    'showonlyactiveenrolconfig' => false,
                    'showonlyactiveenrolpreference' => true,
                    'viewsuspendeduserscapability' => true,
                    'expected' => true,
                ],
            'Disabled showonlyactiveenrol setting; disabled showonlyactiveenrol preference; view suspended users capability' =>
                [
                    'showonlyactiveenrolconfig' => false,
                    'showonlyactiveenrolpreference' => false,
                    'viewsuspendeduserscapability' => true,
                    'expected' => false,
                ],
            'Disabled showonlyactiveenrol setting; disabled showonlyactiveenrol preference; no view suspended users capability' =>
                [
                    'showonlyactiveenrolconfig' => false,
                    'showonlyactiveenrolpreference' => false,
                    'viewsuspendeduserscapability' => false,
                    'expected' => true,
                ],
            'Enabled showonlyactiveenrol setting; enabled showonlyactiveenrol preference; no view suspended users capability' =>
                [
                    'showonlyactiveenrolconfig' => true,
                    'showonlyactiveenrolpreference' => true,
                    'viewsuspendeduserscapability' => false,
                    'expected' => true,
                ],
        ];
    }

    /**
     * Get a plenum instance.
     *
     * @param array $config
     * @return plenum
     */
    protected function get_plenum_instance(array $config = []): plenum {
        $this->resetAfterTest();

        $datagenerator = $this->getDataGenerator();
        $course = $datagenerator->create_course();
        $plenum = $datagenerator->create_module('plenum', array_merge($config, ['course' => $course->id]));
        $cm = get_coursemodule_from_instance('plenum', $plenum->id);
        $context = context_module::instance($cm->id);

        return \core\di::get(\mod_plenum\manager::class)->get_plenum($context, $cm, $course, $plenum);
    }
}
