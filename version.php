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

$plugin->component = 'mod_biblioclubrubook';  // Full name of the plugin.
$plugin->version = 2024071501; // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2016120500; // Requires Moodle 3.2.
$plugin->maturity = MATURITY_STABLE; // Maturity level of this plugin version.
$plugin->release = '2024-07-15'; // Human readable version name.
$plugin->dependencies = array(
    'block_biblioclub_ru' => 2020083102,
);
