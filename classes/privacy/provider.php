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



namespace mod_biblioclubrubook\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    /**
     * Describe identity, session and reading data sent to the external service.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('biblioclub.ru', [
            'user_id' => 'privacy:metadata:external:userid',
            'login' => 'privacy:metadata:external:login',
            'first_name' => 'privacy:metadata:external:firstname',
            'last_name' => 'privacy:metadata:external:lastname',
            'parent_name' => 'privacy:metadata:external:middlename',
            'domain' => 'privacy:metadata:external:domain',
            'time' => 'privacy:metadata:external:time',
            'type' => 'privacy:metadata:external:type',
            'cookie' => 'privacy:metadata:external:cookie',
            'fingerprint' => 'privacy:metadata:external:fingerprint',
            'uid' => 'privacy:metadata:external:uid',
            'query' => 'privacy:metadata:external:query',
            'bookid' => 'privacy:metadata:external:bookid',
            'pages' => 'privacy:metadata:external:pages',
        ], 'privacy:metadata:external');
        return $collection;
    }

    /**
     * The module stores no per-user data in its own database tables.
     *
     * @param int $userid User ID.
     * @return contextlist Empty context list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * There are no locally stored users to enumerate.
     *
     * @param userlist $userlist Context and users.
     */
    public static function get_users_in_context(userlist $userlist) {
    }

    /**
     * External service data cannot be exported by this plugin.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
    }

    /**
     * There is no local per-user activity data to delete.
     *
     * @param \context $context Approved context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
    }

    /**
     * There is no local per-user activity data to delete.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
    }

    /**
     * There is no local per-user activity data to delete.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
    }
}
