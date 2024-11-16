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

declare(strict_types=1);

namespace mod_plenum\grade;

use advanced_testcase;
use core_grades\component_gradeitems;
use coding_exception;

/**
 * Unit tests for mod_plenum\grades\gradeitems.
 *
 * @package   mod_plenum
 * @category  test
 * @copyright 2024 Daniel Thies <dethies@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \mod_plenum\grades\gradeitems
 * @covers    \mod_plenum\grades\plenum_gradeitem
 * @group      mod_plenum
 */
final class gradeitems_test extends advanced_testcase {
    /**
     * Ensure that the mappings are present and correct.
     */
    public function test_get_itemname_mapping_for_component(): void {
        $mappings = component_gradeitems::get_itemname_mapping_for_component('mod_plenum');
        $this->assertIsArray($mappings);
        $this->assertCount(1, $mappings);
        $expected = [0 => 'plenum'];
        // Verify each expected element exists and its value matches.
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $mappings);
            $this->assertSame($value, $mappings[$key]);
        }
    }

    /**
     * Ensure that the advanced grading only applies to the relevant items.
     */
    public function test_get_advancedgrading_itemnames_for_component(): void {
        $mappings = component_gradeitems::get_advancedgrading_itemnames_for_component('mod_plenum');
        $this->assertIsArray($mappings);
        $this->assertCount(1, $mappings);
        $this->assertContains('plenum', $mappings);
        $this->assertNotContains('rating', $mappings);
    }

    /**
     * Ensure that the correct items are identified by is_advancedgrading_itemname.
     *
     * @dataProvider is_advancedgrading_itemname_provider
     * @param string $itemname
     * @param bool $expected
     */
    public function test_is_advancedgrading_itemname(string $itemname, bool $expected): void {
        $this->assertEquals(
            $expected,
            component_gradeitems::is_advancedgrading_itemname('mod_plenum', $itemname)
        );
    }

    /**
     * Data provider for tests of is_advancedgrading_itemname.
     *
     * @return array
     */
    public static function is_advancedgrading_itemname_provider(): array {
        return [
            'Plenary meeting grading is advanced' => [
                'plenum',
                true,
            ],
        ];
    }
}
