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

namespace mod_plenum\reportbuilder\local\systemreports;

use core_reportbuilder\local\helpers\database;
use core_reportbuilder\local\report\column;
use mod_plenum\reportbuilder\local\entities\motion;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\system_report;
use html_writer;

/**
 * Motions system report class implementation.
 *
 * @package    mod_plenum
 * @copyright  2025 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class motions extends system_report {
    /** @var \stdClass the course to constrain the report to. */
    protected \stdClass $course;

    /** @var \stdClass the course module to constrain the report to. */
    protected \stdClass $cm;

    /** @var int the usage count for the tool represented in a row, and set by row_callback(). */
    protected int $perrowtoolusage = 0;

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        global $DB, $CFG;

        $this->course = get_course($this->get_context()->get_course_context()->instanceid);
        $this->cm = get_coursemodule_from_id('plenum', $this->get_context()->instanceid);

        // Our main entity, it contains all the column definitions that we need.
        $entitymain = new motion();
        $entitymainalias = $entitymain->get_table_alias('plenum_motion');

        $this->set_main_table('plenum_motion', $entitymainalias);
        $this->add_entity($entitymain);

        // Join the user entity.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$entitymainalias}.usercreated"));

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns($entitymain);
        $this->add_filters();
        $this->add_actions();

        // We need id and plenum id in the actions column, without entity prefixes, so add these here.
        $this->add_base_fields("{$entitymainalias}.id, {$entitymainalias}.plenum, {$entitymainalias}.status");

        // Scope the report to the module context.
        $plenumparam = database::generate_param_name();
        $wheresql = "{$entitymainalias}.plenum = :{$plenumparam} AND NOT {$entitymainalias}.status = 0";
        $params = [
            $plenumparam => $this->cm->instance,
        ];

        if ($groupmode = groups_get_activity_groupmode($this->cm)) {
            if ($groupid = groups_get_activity_group($this->cm)) {
                $groupparam = database::generate_param_name();
                $wheresql .= " AND {$entitymainalias}.groupid = :{$groupparam}";
                $params += [$groupparam => $groupid];
            }
        }

        // Restrict to user if user id supplied.
        if (
            ($parameters = $this->get_parameters())
            && !empty($parameters['userid'])
        ) {
            $userparam = database::generate_param_name();
            $params += [$userparam => $parameters['userid']];
            $wheresql .= " AND {$entitymainalias}.usercreated = :{$userparam}";
        }

        $this->add_base_condition_sql($wheresql, $params);

        $this->set_downloadable(false, get_string('pluginname', 'mod_plenum'));
        $this->set_default_per_page(30);
        $this->set_default_no_results_notice(null);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('mod/plenum:preside', $this->get_context());
    }

    /**
     * Adds the columns we want to display in the report.
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     * @param motion $motionentity
     * @return void
     */
    protected function add_columns(motion $motionentity): void {
        $entitymainalias = $motionentity->get_table_alias('plenum_motion');

        if (
            ($parameters = $this->get_parameters())
            && !empty($parameters['userid'])
        ) {
            $entity = $this->get_entity('motion');
            foreach (['type', 'timemodified', 'status', 'parent'] as $columnname) {
                $this->add_column($entity->get_column($columnname)->set_is_sortable(false));
            }
        } else {
            $columns = [
                'motion:type',
                'motion:timemodified',
                'user:fullname',
                'motion:status',
                'motion:parent',
            ];

            $this->add_columns_from_entities($columns);
        }


        // Default sorting.
        $this->set_initial_sort_column('motion:timemodified', SORT_ASC);
    }

    /**
     * Add any actions for this report.
     *
     * @return void
     */
    protected function add_actions(): void {
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        if (
            ($parameters = $this->get_parameters())
            && !empty($parameters['userid'])
        ) {
            return;
        }

        $this->add_filters_from_entities([
            'user:fullname',
            'motion:type',
            'motion:status',
            'motion:timemodified',
            'motion:parent',
        ]);
    }
}
