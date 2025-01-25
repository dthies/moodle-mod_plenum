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

namespace plenumform_deft\privacy;

use context_module;
use core_grades\component_gradeitem;
use mod_plenum\plenum;
use core_privacy\tests\provider_testcase;
use mod_plenum\privacy\provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use mod_plenum\motion;

/**
 * Tests for the Plenary meeting privacy provider.
 *
 * @package   plenumform_deft
 * @copyright Daniel Thies <dethies@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \plenumform_deft\privacy\provider
 * @group     mod_plenum
 */
final class provider_test extends provider_testcase {
    /** @var array */
    protected $users = [];
    /** @var array */
    protected $plena = [];
    /** @var array */
    protected $contexts = [];

    /**
     * Set up for each test.
     */
    public function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();

        $dg = $this->getDataGenerator();
        $course = $dg->create_course();
        $pg = $dg->get_plugin_generator('mod_plenum');
        $generator = $dg->get_plugin_generator('plenumform_deft');

        $this->users[1] = $dg->create_user();
        $this->users[2] = $dg->create_user();
        $this->users[3] = $dg->create_user();
        $this->users[4] = $dg->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $dg->enrol_user($this->users[1]->id, $course->id, $studentrole->id, 'manual');
        $dg->enrol_user($this->users[2]->id, $course->id, $studentrole->id, 'manual');
        $dg->enrol_user($this->users[3]->id, $course->id, $studentrole->id, 'manual');
        $dg->enrol_user($this->users[4]->id, $course->id, $studentrole->id, 'manual');

        $this->plena[1] = $this->create_plenum_instance(['course' => $course->id]);
        $this->plena[2] = $this->create_plenum_instance(['course' => $course->id]);
        $this->plena[3] = $this->create_plenum_instance(['course' => $course->id]);
        $this->plena[4] = $this->create_plenum_instance(['course' => $course->id]); // Empty.

        // User1.
        $this->setUser($this->users[1]);
        $open1 = $pg->create_motion([
            'plenumid' => $this->plena[1]->get_course_module()->id,
            'status' => motion::STATUS_PENDING,
        ]);
        $open2 = $pg->create_motion([
            'plenumid' => $this->plena[2]->get_course_module()->id,
            'status' => motion::STATUS_PENDING,
        ]);
        $this->setUser($this->users[1]);
        $generator->create_peer($open1);

        // User 2.
        $this->setUser($this->users[2]);
        $order1 = $pg->create_motion([
            'plenumid' => $this->plena[1]->get_course_module()->id,
            'parent' => $open1->get('id'),
            'status' => motion::STATUS_PENDING,
            'type' => 'order',
        ]);
        $generator->create_peer($order1);
        $order2 = $pg->create_motion([
            'plenumid' => $this->plena[2]->get_course_module()->id,
            'parent' => $open2->get('id'),
            'status' => motion::STATUS_DRAFT,
            'type' => 'order',
        ]);
    }

    /**
     * Test getting the contexts for a user.
     */
    public function test_get_contexts_for_userid(): void {

        // Get contexts for the first user.
        $contextids = provider::get_contexts_for_userid($this->users[1]->id)->get_contextids();
        $this->assertEqualsCanonicalizing([
            $this->plena[1]->get_context()->id,
            $this->plena[2]->get_context()->id,
        ], $contextids);

        // Get contexts for the second user.
        $contextids = provider::get_contexts_for_userid($this->users[2]->id)->get_contextids();
        $this->assertEqualsCanonicalizing([
            $this->plena[1]->get_context()->id,
            $this->plena[2]->get_context()->id,
        ], $contextids);

        // Get contexts for the third user.
        $contextids = provider::get_contexts_for_userid($this->users[3]->id)->get_contextids();
        $this->assertEqualsCanonicalizing([
        ], $contextids);
    }

    /**
     * Export data for user 1
     */
    public function test_export_user_data1(): void {

        // Export all contexts for the first user.
        $contextids = array_values(array_map(function ($plenum) {
            return $plenum->get_context()->id;
        }, $this->plena));
        $appctx = new approved_contextlist($this->users[1], 'plenumform_deft', $contextids);
        provider::export_user_data($appctx);

        // Validate exported data for user 1.
        writer::reset();
        $this->setUser($this->users[1]);
        $context = $this->plena[1]->get_context();
        $component = 'plenumform_deft';
        $writer = writer::with_context($context);
        $this->assertFalse($writer->has_any_data());

        $this->export_context_data_for_user($this->users[1]->id, $context, $component);
        $this->assertTrue($writer->has_any_data());

        $this->assertEquals('Plenary meeting 1', $writer->get_data()->name);
        $subcontext = [
            get_string('privacy:connections', 'plenumform_deft'),
        ];
        $data = $writer->get_data($subcontext);
        $this->assertCount(1, $data->connections);

        $this->export_context_data_for_user($this->users[2]->id, $context, $component);
        $this->assertTrue($writer->has_any_data());

        $this->assertEquals('Plenary meeting 1', $writer->get_data()->name);
        $subcontext = [
            get_string('privacy:connections', 'plenumform_deft'),
        ];
        $data = $writer->get_data($subcontext);
        $this->assertCount(1, $data->connections);
    }

    /**
     * Test for delete_data_for_user().
     */
    public function test_delete_data_for_user(): void {
        // User 1.
        $appctx = new approved_contextlist(
            $this->users[1],
            'mod_plenum',
            [$this->plena[1]->get_context()->id, $this->plena[2]->get_context()->id]
        );
        provider::delete_data_for_user($appctx);

        provider::export_user_data($appctx);
        $this->assertTrue(writer::with_context($this->plena[1]->get_context())->has_any_data());
        $this->assertTrue(writer::with_context($this->plena[2]->get_context())->has_any_data());

        // User 2.
        writer::reset();
        $appctx = new approved_contextlist(
            $this->users[2],
            'mod_plenum',
            [$this->plena[1]->get_context()->id, $this->plena[2]->get_context()->id]
        );
        provider::delete_data_for_user($appctx);

        provider::export_user_data($appctx);
        // Motions are not deleted unless they are draft.
        $this->assertTrue(writer::with_context($this->plena[1]->get_context())->has_any_data());
        $this->assertFalse(writer::with_context($this->plena[2]->get_context())->has_any_data());
    }

    /**
     * Test for delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context(): void {
        provider::delete_data_for_all_users_in_context($this->plena[1]->get_context());

        $appctx = new approved_contextlist(
            $this->users[1],
            'mod_plenum',
            [$this->plena[1]->get_context()->id, $this->plena[2]->get_context()->id]
        );
        provider::export_user_data($appctx);
        $this->assertFalse(writer::with_context($this->plena[1]->get_context())->has_any_data());
        $this->assertTrue(writer::with_context($this->plena[2]->get_context())->has_any_data());

        writer::reset();
        $appctx = new approved_contextlist($this->users[2], 'mod_plenum', [$this->plena[1]->get_context()->id]);
        provider::export_user_data($appctx);
        $this->assertFalse(writer::with_context($this->plena[1]->get_context())->has_any_data());

        writer::reset();
        $appctx = new approved_contextlist($this->users[3], 'mod_plenum', [$this->plena[1]->get_context()->id]);
        provider::export_user_data($appctx);
        $this->assertFalse(writer::with_context($this->plena[1]->get_context())->has_any_data());
    }

    /**
     * Create a plenum instance.
     *
     * @param array $config
     * @return plenum
     */
    protected function create_plenum_instance(array $config = []): plenum {
        $this->resetAfterTest();

        $datagenerator = $this->getDataGenerator();
        if (!empty($config['course'])) {
            $course = get_course($config['course']);
        } else {
            $course = $datagenerator->create_course();
        }
        $plenum = $datagenerator->create_module('plenum', array_merge($config, ['course' => $course->id]));
        $cm = get_coursemodule_from_instance('plenum', $plenum->id);
        $context = context_module::instance($cm->id);

        return \core\di::get(\mod_plenum\manager::class)->get_plenum($context, $cm, $course, $plenum);
    }
}
