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


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/biblioclubrubook/backup/moodle2/restore_biblioclubrubook_stepslib.php');

/**
 * Restore task
 */
class restore_biblioclubrubook_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Module only has one structure step.
        $this->add_step(new restore_biblioclubrubook_activity_structure_step('biblioclubrubook_structure', 'biblioclubrubook.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('biblioclubrubook',
	        array('intro', 'bookid', 'bookdescription', 'bookpage', 'showbibliography',
		        'bibliographyposition', 'bookbiblio', 'bookcover'));

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('BIBLIOCLUBRUBOOKINDEX', '/mod/biblioclubrubook/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('BIBLIOCLUBRUBOOKVIEWBYID', '/mod/biblioclubrubook/view.php?id=$1', 'course_module');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the restore_logs_processor when restoring
     * course logs. It must return one array
     * of restore_log_rule objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('biblioclubrubook', 'view all', 'index.php?id={course}', null);
        $rules[] = new restore_log_rule('biblioclubrubook', 'view', 'view.php?id={course_module}', '{biblioclubrubook}');

        return $rules;
    }
}
