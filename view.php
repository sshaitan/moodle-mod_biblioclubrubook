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


require('../../config.php');
require_once("$CFG->dirroot/mod/biblioclubrubook/lib.php");
require_once("$CFG->dirroot/mod/biblioclubrubook/locallib.php");
require_once("$CFG->dirroot/mod/biblioclubrubook/classes/ub_api.php");

function error_redirect_handler($bookId){
	
	$url = new moodle_url(\ub_api::$authurl, [
		'page' => 'book_red',
		'id' => intval($bookId)
	]);
	redirect($url);
}

$id = required_param('id', PARAM_INT); // Course module ID.
$forceview = optional_param('forceview', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('biblioclubrubook', $id, 0, false, MUST_EXIST);
$book = $DB->get_record('biblioclubrubook', array('id' => $cm->instance), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/biblioclubrubook:view', $context);

// Completion and trigger events.
biblioclubrubook_view($book, $course, $cm, $context);

$PAGE->set_url('/mod/biblioclubrubook/view.php', array('id' => $cm->id));

$params = array(
	'contextid' => $context->id,
	'documentid' => $book->bookid,
);
if ($book->bookpage) {
	$params['page'] = $book->bookpage;
}

// подгружаем ссылки на просмотр
$cookie = \ub_api::get_auth_cookie();
if (empty($cookie)) {
	// что-то не так с аутентификацией
	error_redirect_handler($book->bookid);
}

$ub_links = \ub_api::getLinks($cookie, $book->bookid, $book->bookpage);

if (empty($ub_links)){
	// не удалось получить ссылки
	error_redirect_handler($book->bookid);
}

if (!course_get_format($course)->has_view_page()) {
	if (has_capability('moodle/course:manageactivities', $context)
		|| has_capability('moodle/course:update', $context->get_course_context())
	) {
		$forceview = true;
	}
}

$url = new moodle_url('https:'.$ub_links['url']);
if (!$forceview) {
	redirect($url);
}

biblioclubrubook_print_workaround($book, $cm, $course, $ub_links);