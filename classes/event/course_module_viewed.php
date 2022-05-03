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


namespace mod_biblioclubrubook\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Book view event
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'biblioclubrubook';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * This is used when restoring course logs where it is required that we
     * map the objectid to it's new value in the new course.
     *
     * Does nothing in the base class except display a debugging message warning
     * the user that the event does not contain the required functionality to
     * map this information. For events that do not store an objectid this won't
     * be called, so no debugging message will be displayed.
     *
     * Example of usage:
     *
     * return array('db' => 'assign_submissions', 'restore' => 'submission');
     *
     * If the objectid can not be mapped during restore set the value to \core\event\base::NOT_MAPPED, example -
     *
     * return array('db' => 'some_table', 'restore' => \core\event\base::NOT_MAPPED);
     *
     * Note - it isn't necessary to specify the 'db' and 'restore' values in this case, so you can also use -
     *
     * return \core\event\base::NOT_MAPPED;
     *
     * The 'db' key refers to the database table and the 'restore' key refers to
     * the name of the restore element the objectid is associated with. In many
     * cases these will be the same.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return array('db' => 'biblioclubrubook', 'restore' => 'biblioclubrubook');
    }
}
