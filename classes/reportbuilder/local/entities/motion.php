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

namespace mod_plenum\reportbuilder\local\entities;

use core_reportbuilder\local\helpers\database;
use core_reportbuilder\local\filters\{date, number, select, text};
use html_writer;
use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\helpers\format;
use mod_plenum\plugininfo\plenumtype;
use stdClass;

/**
 * Motions class implementation.
 *
 * Defines all the columns and filters that can be added to reports that use this entity.
 *
 * @package    mod_plenum
 * @copyright  2025 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class motion extends base {
    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'plenum_motion',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('motions', 'mod_plenum');
    }

    /**
     * Initialize the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('plenum_motion');

        // Type column.
        $columns[] = (new column(
            'type',
            new lang_string('type', 'mod_plenum'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.type, {$tablealias}.id")
            ->add_callback(function ($type, $row) {
                if (empty($type)) {
                    return '';
                }
                return html_writer::link(
                    new \moodle_url('/mod/plenum/motion.php', ['id' => $row->id]),
                    new lang_string('pluginname', "plenumtype_$type"),
                    [
                        'data-action' => 'preview',
                    ]
                );
            })
            ->set_is_sortable(true);

        // Time created column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'core'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        // Time created column.
        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'mod_plenum'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$tablealias}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        // User column.
        $columns[] = (new column(
            'usermodified',
            new lang_string('user'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.usermodified")
            ->set_is_sortable(true);

        // Status column.
        $columns[] = (new column(
            'status',
            new lang_string('status'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.status")
            ->add_callback(function ($status, $row) {
                switch ($status) {
                    case \mod_plenum\motion::STATUS_ADOPT:
                        return new lang_string('statusadopted', 'mod_plenum');
                    case \mod_plenum\motion::STATUS_OPEN:
                        return new lang_string('statusopen', 'mod_plenum');
                    case \mod_plenum\motion::STATUS_CLOSED:
                        return new lang_string('statusclosed', 'mod_plenum');
                    case \mod_plenum\motion::STATUS_DECLINE:
                        return new lang_string('statusdeclined', 'mod_plenum');
                    case \mod_plenum\motion::STATUS_PENDING:
                        return new lang_string('statuspending', 'mod_plenum');
                    default:
                        return $status;
                }
            })
            ->set_is_sortable(true);

        // Join the plenum_motion table to add parent information.
        $parenttablealias = database::generate_alias();
        $joinsql = "LEFT JOIN {plenum_motion} {$parenttablealias}
                           ON ({$parenttablealias}.id = {$tablealias}.parent)";
        $this->add_join($joinsql);

        // Parent column.
        $columns[] = (new column(
            'parent',
            new lang_string('precedingmotion', 'mod_plenum'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join($joinsql)
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.parent, {$parenttablealias}.type AS parenttype")
            ->add_callback(function ($parent, $row) {
                if (empty($parent)) {
                    return '';
                }
                return html_writer::link(
                    new \moodle_url('/mod/plenum/motion.php', ['id' => $parent]),
                    new lang_string('pluginname', "plenumtype_$row->parenttype"),
                    [
                        'data-action' => 'preview',
                    ]
                );
            })
            ->set_is_sortable(true);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('plenum_motion');

        $types = array_map(function ($type) {
            return new lang_string('pluginname', "plenumtype_$type");
        }, plenumtype::get_enabled_plugins());

        // Join the plenum_motion table to add parent information.
        $parenttablealias = database::generate_alias();
        $joinsql = "LEFT JOIN {plenum_motion} {$parenttablealias}
                           ON ({$parenttablealias}.id = {$tablealias}.parent)";

        return [
            // Motion type filter.
            (new filter(
                select::class,
                'type',
                new lang_string('type', 'mod_plenum'),
                $this->get_entity_name(),
                "{$tablealias}.type"
            ))
                ->add_joins($this->get_joins())
                ->set_options($types),

            // Preceding motion type filter.
            (new filter(
                select::class,
                'parent',
                new lang_string('precedingmotion', 'mod_plenum'),
                $this->get_entity_name(),
                "{$parenttablealias}.type"
            ))
                ->add_joins($this->get_joins())
                ->add_join($joinsql)
                ->set_options($types),

            // Motion status filter.
            (new filter(
                select::class,
                'status',
                new lang_string('status'),
                $this->get_entity_name(),
                "{$tablealias}.status"
            ))
                ->add_joins($this->get_joins())
                ->set_options([
                    \mod_plenum\motion::STATUS_ADOPT => new lang_string('statusadopted', 'mod_plenum'),
                    \mod_plenum\motion::STATUS_OPEN => new lang_string('statusopen', 'mod_plenum'),
                    \mod_plenum\motion::STATUS_CLOSED => new lang_string('statusclosed', 'mod_plenum'),
                    \mod_plenum\motion::STATUS_DECLINE => new lang_string('statusdeclined', 'mod_plenum'),
                    \mod_plenum\motion::STATUS_PENDING => new lang_string('statuspending', 'mod_plenum'),
                ]),

            // Time created.
            $filters[] = (new filter(
                date::class,
                'timecreated',
                new lang_string('timecreated', 'core_reportbuilder'),
                $this->get_entity_name(),
                "{$tablealias}.timecreated"
            ))
                ->add_joins($this->get_joins())
                ->set_limited_operators([
                    date::DATE_ANY,
                    date::DATE_RANGE,
                    date::DATE_LAST,
                    date::DATE_CURRENT,
                ]),

            // Time modified.
            $filters[] = (new filter(
                date::class,
                'timemodified',
                new lang_string('timemodified', 'core_reportbuilder'),
                $this->get_entity_name(),
                "{$tablealias}.timemodified"
            ))
                ->add_joins($this->get_joins())
                ->set_limited_operators([
                    date::DATE_ANY,
                    date::DATE_RANGE,
                    date::DATE_LAST,
                    date::DATE_CURRENT,
                ]),
        ];
    }
}
