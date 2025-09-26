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

/**
 * Manage Plenum form plugins
 *
 * @package    mod_plenum
 * @copyright  2023 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_plenum\admin;

use flexible_table;
use moodle_url;

/**
 * Class manage_plenumform_plugins_page.
 *
 * @copyright  2023 Daniel Thies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_plenumform_plugins_page extends \admin_setting {
    /**
     * Class admin_page_manageplenu_plugims_page constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct(
            'manageplenum',
            new \lang_string('manageplenumformplugins', 'mod_plenum'),
            '',
            ''
        );
    }

    /**
     * Get setting
     *
     * @return bool
     */
    public function get_setting(): bool {
        return true;
    }

    /**
     * Get default setting
     *
     * @return bool
     */
    public function get_defaultsetting(): bool {
        return true;
    }

    /**
     * Write setting
     *
     * @param stdClass $data
     * @return string
     */
    public function write_setting($data): string {
        // Do not write any setting.
        return '';
    }

    /**
     * Find if related
     *
     * @param string $query
     * @return bool
     */
    public function is_related($query): bool {
        if (parent::is_related($query)) {
            return true;
        }
        $types = \core_plugin_manager::instance()->get_plugins_of_type('plenumform');
        foreach ($types as $type) {
            if (
                strpos($type->component, $query) !== false ||
                    strpos(\core_text::strtolower($type->displayname), $query) !== false
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Output HTML
     *
     * @param stdClass $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = ''): string {
        global $OUTPUT;

        $return = '';

        $pluginmanager = \core_plugin_manager::instance();
        $types = $pluginmanager->get_plugins_of_type('plenumform');
        if (empty($types)) {
            return get_string('noquestionbanks', 'question');
        }
        $txt = get_strings(['settings', 'name', 'enable', 'disable', 'default']);
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');

        $table = new flexible_table('manageplenumformplugins');
        $table->define_headers([$txt->name, $txt->enable, $txt->settings, $txt->uninstall]);
        $table->define_baseurl(new moodle_url('/admin/settings.php', ['section' => 'manageplenumformplugins']));
        $table->set_attribute('class', 'manageplenumformtable generaltable admintable m-3');
        $table->define_columns([
            'strtypename',
            'hideshow',
            'settings',
            'uninstall',
        ]);
        $table->setup();

        $totalenabled = 0;
        $count = 0;
        foreach ($types as $type) {
            if ($type->is_enabled() && $type->is_installed_and_upgraded()) {
                $totalenabled++;
            }
        }

        foreach ($types as $type) {
            $url = new moodle_url('/mod/plenum/subplugins.php', [
                'sesskey' => sesskey(),
                'name' => $type->name,
                'type' => 'plenumform',
            ]);

            $class = '';
            if (
                $pluginmanager->get_plugin_info('plenumform_' . $type->name)->get_status() ===
                    \core_plugin_manager::PLUGIN_STATUS_MISSING
            ) {
                $strtypename = $type->displayname . ' (' . get_string('missingfromdisk') . ')';
            } else {
                $strtypename = $type->displayname;
            }

            if ($type->is_enabled()) {
                $hideshow = \html_writer::link(
                    $url->out(false, ['action' => 'disable']),
                    $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', ['class' => 'iconsmall'])
                );
            } else {
                $class = 'dimmed_text';
                $hideshow = \html_writer::link(
                    $url->out(false, ['action' => 'enable']),
                    $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', ['class' => 'iconsmall'])
                );
            }

            $settings = '';
            if ($type->get_settings_url()) {
                $settings = \html_writer::link($type->get_settings_url(), $txt->settings);
            }

            $uninstall = '';
            if (
                $uninstallurl = \core_plugin_manager::instance()->get_uninstall_url(
                    'plenumform_' . $type->name,
                    'manage'
                )
            ) {
                $uninstall = \html_writer::link($uninstallurl, $txt->uninstall);
            }

            $row = [$strtypename, $hideshow, $settings, $uninstall];
            $table->add_data($row, $class);
            $count++;
        }

        ob_start();
        $table->finish_output();
        $return .= ob_get_contents();
        ob_end_clean();

        return highlight($query, $return);
    }
}
