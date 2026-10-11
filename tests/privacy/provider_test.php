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

namespace mod_biblioclubrubook\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\types\external_location;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;

/**
 * External-only privacy provider tests.
 *
 * @package mod_biblioclubrubook
 * @copyright 2026 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(provider::class)]
final class provider_test extends \advanced_testcase {
    /** The provider declares actual identity and reading data transferred to EBS. */
    public function test_metadata(): void {
        $this->resetAfterTest();
        $items = provider::get_metadata(new collection('mod_biblioclubrubook'))->get_collection();
        $this->assertCount(1, $items);
        $external = reset($items);
        $this->assertInstanceOf(external_location::class, $external);
        $this->assertSame('biblioclub.ru', $external->get_name());
        foreach (['user_id', 'login', 'parent_name', 'cookie', 'fingerprint', 'query', 'bookid', 'pages'] as $field) {
            $this->assertArrayHasKey($field, $external->get_privacy_fields());
        }
        $this->assertTrue((new \core_privacy\manager())->component_is_compliant('mod_biblioclubrubook'));
    }

    /** Requests do not invent local user data or delete course resources. */
    public function test_local_requests_are_empty(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $context = \context_system::instance();
        $course = $this->getDataGenerator()->create_course();
        $resourceid = $DB->insert_record('biblioclubrubook', (object) [
            'course' => $course->id, 'name' => 'Shared course resource', 'bookid' => '123',
        ]);
        $count = $DB->count_records('biblioclubrubook');
        $this->assertEmpty(provider::get_contexts_for_userid($user->id)->get_contextids());
        $users = new userlist($context, 'mod_biblioclubrubook');
        provider::get_users_in_context($users);
        $this->assertEmpty($users->get_userids());
        $contexts = new approved_contextlist($user, 'mod_biblioclubrubook', [$context->id]);
        provider::export_user_data($contexts);
        provider::delete_data_for_user($contexts);
        provider::delete_data_for_users(new approved_userlist($context, 'mod_biblioclubrubook', [$user->id]));
        provider::delete_data_for_all_users_in_context($context);
        $this->assertEquals($count, $DB->count_records('biblioclubrubook'));
        $this->assertTrue($DB->record_exists('biblioclubrubook', ['id' => $resourceid]));
    }
}
