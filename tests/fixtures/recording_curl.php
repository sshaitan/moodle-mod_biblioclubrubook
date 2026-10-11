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

defined('MOODLE_INTERNAL') || die();

/**
 * Offline HTTP client recording request parameters.
 *
 * @package mod_biblioclubrubook
 * @copyright 2026 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recording_curl extends \curl {
    /** @var array Recorded request. */
    public $request = [];
    /** @var string Response returned to the caller. */
    public $body = '';
    /** @var int Number of requests. */
    public $calls = 0;

    public function get($url, $params = [], $options = []) {
        $this->record('GET', $url, $params, $options);
        return $this->body;
    }

    public function post($url, $params = '', $options = []) {
        $this->record('POST', $url, $params, $options);
        return $this->body;
    }

    /**
     * Record the complete request, including headers set through the Moodle API.
     *
     * @param string $method HTTP method.
     * @param string $url Request URL.
     * @param mixed $params Request body or parameters.
     * @param array $options cURL options.
     */
    private function record($method, $url, $params, $options) {
        $this->calls++;
        $this->request = compact('method', 'url', 'params', 'options');
        $this->request['headers'] = $this->header;
    }
}
