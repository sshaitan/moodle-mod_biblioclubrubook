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

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/biblioclubrubook/lib.php');
	
	$userDomain = get_config('block_biblioclub_ru', 'domain');

    $settings->add(new admin_setting_heading(
        'biblioclubrubookmodeditdefaults',
        get_string('modeditdefaults', 'admin'),
        get_string('condifmodeditdefaults', 'admin')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'biblioclubrubook/showbibliography',
        get_string('mod_form_show_bibliography', 'biblioclubrubook'),
        '',
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'biblioclubrubook/forcenewpage',
        get_string('mod_form_force_newpage', 'biblioclubrubook'),
        '',
        0
    ));

    $bibliographypositions = array(
        BIBLIOCLUBRUBOOK_BIBLIOGRAPHY_POSITION_BEFORE => get_string('mod_form_bibliography_position_before', 'biblioclubrubook'),
        BIBLIOCLUBRUBOOK_BIBLIOGRAPHY_POSITION_AFTER => get_string('mod_form_bibliography_position_after', 'biblioclubrubook'),
    );
    $settings->add(new admin_setting_configselect(
        'biblioclubrubook/bibliographyposition',
        get_string('mod_form_bibliography_position', 'biblioclubrubook'),
        '',
        BIBLIOCLUBRUBOOK_BIBLIOGRAPHY_POSITION_BEFORE,
        $bibliographypositions
    ));
	
	$settings->add(new admin_setting_configtext(
		'biblioclubrubook/sandbox_domain_override',
		new lang_string('sandbox_domain_override', 'biblioclubrubook'),
		'',
		$userDomain,
		PARAM_TEXT));
	
}
