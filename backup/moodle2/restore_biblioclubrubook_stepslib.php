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
 * Book from znanium.com module
 *
 * @package mod_znaniumcombook
 * @copyright 2020 Vadim Dvorovenko
 * @copyright 2020 ООО «ЗНАНИУМ»
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore task step
 */
class restore_znaniumcombook_activity_structure_step extends restore_activity_structure_step {

    /**
     * Adds support for the 'activity' path that is common to all the activities
     * and will be processed globally here
     */
    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('znaniumcombook', '/activity/znaniumcombook');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores book
     * @param array|object $data
     */
    protected function process_znaniumcombook($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        // Insert the znaniumcombook record.
        $newitemid = $DB->insert_record('znaniumcombook', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * This method will be executed after the whole structure step have been processed
     *
     * After execution method for code needed to be executed after the whole structure
     * has been processed. Useful for cleaning tasks, files process and others. Simply
     * overwrite in in your steps if needed
     */
    protected function after_execute() {
        $this->add_related_files('mod_znaniumcombook', 'intro', null);
    }
}
