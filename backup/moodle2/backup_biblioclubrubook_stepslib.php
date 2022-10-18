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
 * Book from biblioclub.ru module
 *
 * @package mod_biblioclubrubook
 * @copyright 2022 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Backup task step
 */
class backup_biblioclubrubook_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure to be processed by this backup step.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Module stores no user info.

        // Define each element separated.
        $biblioclubrubook = new backup_nested_element('biblioclubrubook', array('id'), array(
            'name', 'timemodified', 'intro', 'introformat', 'bookid', 'bookdescription', 'bookpage',
            'showbibliography', 'bibliographyposition', 'bookbiblio', 'bookcover'));

        // Define sources.
        $biblioclubrubook->set_source_table('biblioclubrubook', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations.
        $biblioclubrubook->annotate_files('mod_biblioclubrubook', 'intro', null); // This file area hasn't itemid.

        // Return the root element, wrapped into standard activity structure.
        return $this->prepare_activity_structure($biblioclubrubook);

    }
}
