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
 * API authentication and HTTP regression tests. No external service is contacted.
 *
 * @package mod_biblioclubrubook
 * @copyright 2026 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(ub_api::class)]
final class ub_api_test extends \advanced_testcase {
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user(['username' => 'reviewer']));
        require_once($CFG->libdir . '/filelib.php');
        require_once(__DIR__ . '/fixtures/recording_curl.php');
        require_once(__DIR__ . '/fixtures/testable_ub_api.php');
        unset($_SESSION['mod_biblioclubrubook_auth']);
        set_config('domain', 'example.invalid', 'block_biblioclub_ru');
        set_config('secretkey', 'unit-test-only', 'block_biblioclub_ru');
        testable_ub_api::$client = new recording_curl();
    }

    protected function tearDown(): void {
        unset($_SESSION['mod_biblioclubrubook_auth']);
        testable_ub_api::$client = null;
        parent::tearDown();
    }

    /** The real class is autoloaded under its component, not the global namespace. */
    public function test_autoload(): void {
        $this->assertTrue(class_exists(ub_api::class));
        $this->assertFalse(class_exists('ub_api'));
        $this->assertSame('https://biblioclub.ru/index.php', ub_api::$authurl);
    }

    /** Capture only library cookies and preserve the fingerprint/UA when reusing a session. */
    public function test_authentication_and_session_reuse(): void {
        $client = testable_ub_api::$client;
        $client->body = "HTTP/1.1 302 Found\r\nSet-Cookie: uinfo=test-user; Secure; HttpOnly\r\n"
            . "Set-Cookie: u_tm=test-time; Secure\r\nSet-Cookie: unrelated=ignore-me\r\n\r\n";
        $cookie = testable_ub_api::get_auth_cookie();
        $this->assertSame('uinfo=test-user; u_tm=test-time', $cookie);
        $auth = $_SESSION['mod_biblioclubrubook_auth'];
        $this->assertSame('GET', $client->request['method']);
        $this->assertFalse($client->request['options']['CURLOPT_FOLLOWLOCATION']);
        $this->assertTrue($client->request['options']['CURLOPT_SSL_VERIFYPEER']);
        $this->assertSame('Moodle Biblioclub plugin', $client->request['options']['CURLOPT_USERAGENT']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $auth['fingerprint']);
        $this->assertContains('X-Sign: ' . $auth['fingerprint'], $client->request['headers']);
        $this->assertSame($cookie, testable_ub_api::get_auth_cookie());
        $this->assertSame(1, $client->calls);
        $this->assertSame($auth, $_SESSION['mod_biblioclubrubook_auth']);
    }

    /** Expired sessions are replaced and missing authentication cookies fail safely. */
    public function test_expired_session_and_failed_auth(): void {
        $_SESSION['mod_biblioclubrubook_auth'] = [
            'expires' => '2000-01-01 00:00:00', 'cookie' => 'uinfo=expired',
            'fingerprint' => 'expired', 'useragent' => 'expired',
        ];
        testable_ub_api::$client->body = "HTTP/1.1 200 OK\r\nSet-Cookie: other=not-auth\r\n\r\n";
        $this->assertNull(testable_ub_api::get_auth_cookie());
        $this->assertSame(1, testable_ub_api::$client->calls);
    }

    /** Cookies, fingerprint, stable UA and TLS verification survive subsequent API requests. */
    public function test_get_request(): void {
        $_SESSION['mod_biblioclubrubook_auth'] = ['fingerprint' => 'test-fingerprint', 'useragent' => 'test-agent'];
        $client = testable_ub_api::$client;
        $client->body = '123';
        $this->assertSame(['data' => '123'], testable_ub_api::curlRequest('https://biblioclub.ru/test', 'uinfo=test'));
        $this->assertSame('GET', $client->request['method']);
        $this->assertContains('Cookie: uinfo=test', $client->request['headers']);
        $this->assertContains('X-Sign: test-fingerprint', $client->request['headers']);
        $this->assertSame('test-agent', $client->request['options']['CURLOPT_USERAGENT']);
        $this->assertTrue($client->request['options']['CURLOPT_SSL_VERIFYPEER']);
    }

    /** Form encoding must not split search terms containing ampersands, plus or equals signs. */
    public function test_post_encoding(): void {
        $client = testable_ub_api::$client;
        $client->body = '{"ok":true}';
        $query = ['query' => 'Русский & English + test=1', 'ids' => [123, 456]];
        $this->assertSame(['ok' => true], testable_ub_api::curlRequest('https://biblioclub.ru/test', 'uinfo=test', $query));
        $this->assertSame('POST', $client->request['method']);
        parse_str($client->request['params'], $params);
        $this->assertCount(1, $params);
        $this->assertSame($query, json_decode($params['p'], true));
        $this->assertContains('Content-Type: application/x-www-form-urlencoded', $client->request['headers']);
    }

    /** Invalid/non-JSON responses never escape as null and crash array-returning callers. */
    public function test_response_decoding(): void {
        $cases = [
            ['false', ['data' => 'false']],
            ['', []],
            ['<html>error</html>', []],
            ['null', []],
            ['{"books":[1]}', ['books' => [1]]],
        ];
        foreach ($cases as list($body, $expected)) {
            testable_ub_api::$client->body = $body;
            $this->assertSame($expected, testable_ub_api::curlRequest('https://biblioclub.ru/test', 'uinfo=test'));
        }
    }

    /** The production factory uses Moodle curl and respects its blocked-host security policy. */
    public function test_moodle_curl_security_policy(): void {
        global $CFG;
        $CFG->curlsecurityblockedhosts = 'biblioclub.ru';
        $this->assertSame([], ub_api::curlRequest('https://biblioclub.ru/test', 'uinfo=test'));
        $this->assertDebuggingCalled();
        $this->assertNull(ub_api::get_auth_cookie());
        $this->assertDebuggingCalled();
    }

    /** Failed authentication returns an empty search result, not a PHP type error. */
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_search_after_auth_failure(): void {
        \curl::mock_response('No authentication cookies');
        $result = search_api::search_books('test');
        $this->assertEmpty($result['publications']);
        $this->assertSame(0, $result['_meta']['pageCount']);
    }
}
