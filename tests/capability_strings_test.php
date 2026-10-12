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

namespace mod_biblioclubrubook;

/**
 * Capability descriptions required by directory validation and Moodle roles.
 *
 * @package mod_biblioclubrubook
 * @copyright 2026 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
final class capability_strings_test extends \basic_testcase {
    /** Every capability must have a non-empty English and Russian description. */
    public function test_declared_capabilities_have_translated_names(): void {
        $plugindir = dirname(__DIR__);
        $capabilities = [];
        require($plugindir . '/db/access.php');
        $this->assertNotEmpty($capabilities);

        foreach (['en', 'ru'] as $language) {
            $string = [];
            require($plugindir . '/lang/' . $language . '/biblioclubrubook.php');
            foreach (array_keys($capabilities) as $capability) {
                $stringid = substr($capability, strlen('mod/'));
                $this->assertArrayHasKey($stringid, $string, $language . ': ' . $stringid);
                $this->assertIsString($string[$stringid]);
                $this->assertNotSame('', trim($string[$stringid]));
            }
        }
    }
}
