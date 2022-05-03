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
 * Print biblioclubrubook info and workaround link when JS not available.
 * @param object $book
 * @param object $cm
 * @param object $course
 * @param array $ub_links
 * @return does not return
 */
function biblioclubrubook_print_workaround($book, $cm, $course, $ub_links) {
    global $OUTPUT;
	
	biblioclubrubook_print_header($book, $cm, $course);
	biblioclubrubook_print_heading($book, $cm, $course);
	biblioclubrubook_print_intro($book, $cm, $course);

    echo html_writer::start_div('text-center');
	echo $ub_links['view'];
    echo html_writer::end_div();

    echo $OUTPUT->footer();
    die;
}

/**
 * Print biblioclubrubook header.
 * @param object $biblioclubrubook
 * @param object $cm
 * @param object $course
 * @return void
 */
function biblioclubrubook_print_header($biblioclubrubook, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$biblioclubrubook->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($biblioclubrubook);
    echo $OUTPUT->header();
}

/**
 * Print biblioclubrubook heading.
 * @param object $biblioclubrubook
 * @param object $cm
 * @param object $course
 * @return void
 */
function biblioclubrubook_print_heading($biblioclubrubook, $cm, $course) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($biblioclubrubook->name), 2);
}

/**
 * Print biblioclubrubook introduction.
 * @param object $book
 * @param object $cm
 * @param object $course
 * @return void
 */
function biblioclubrubook_print_intro($book, $cm, $course) {
    global $OUTPUT;

    $modinfo = get_fast_modinfo($course);
    /** @var cached_cm_info $cminfo */
    $cminfo = $modinfo->cms[$cm->id];
    $intro = $cminfo->content;
    if ($intro) {
        echo $OUTPUT->box_start('mod_introbox', 'biblioclubrubookintro');
        echo $intro;
        echo $OUTPUT->box_end();
    }
}
