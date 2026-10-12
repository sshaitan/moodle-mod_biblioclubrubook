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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Copy to a disposable Moodle 5.2 checkout for structure validation only.
 *
 * @package mod_biblioclubrubook
 * @copyright 2026 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('ABORT_AFTER_CONFIG') || !ABORT_AFTER_CONFIG) {
    throw new RuntimeException('This fixture is only for static plugin validation, not a Moodle installation.');
}

global $CFG;
$CFG = new stdClass();
$CFG->dbtype = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost = 'unused.invalid';
$CFG->dbname = 'unused';
$CFG->dbuser = 'unused';
$CFG->dbpass = '';
$CFG->prefix = 'mdl_';
$CFG->wwwroot = 'http://localhost';
$CFG->dataroot = sys_get_temp_dir() . '/biblioclub-structure-validation';
$CFG->admin = 'admin';

require_once(__DIR__ . '/public/lib/setup.php');
