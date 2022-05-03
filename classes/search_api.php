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


namespace mod_biblioclubrubook;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->dirroot/mod/biblioclubrubook/classes/ub_api.php");
/**
 * Search api class
 */
class search_api extends \external_api
{
	
	
	/**
	 * Returns description of method parameters
	 * @return \external_function_parameters
	 */
	public static function search_books_parameters()
	{
		return new \external_function_parameters(array(
			'searchquery' => new \external_value(PARAM_TEXT, 'Search query', VALUE_REQUIRED),
			'page' => new \external_value(PARAM_INT, 'Results page', VALUE_DEFAULT, 0),
		));
	}
	
	/**
	 * Returns description of method parameters
	 * @return \external_description
	 */
	public static function search_books_returns()
	{
		return new \external_multiple_structure(
			new \external_single_structure(array())
		);
	}
	
	/**
	 * Searches for book
	 *
	 * @param string $searchquery
	 * @param int $page
	 * @return array
	 */
	public static function search_books($searchquery, $page = 0)
	{
		$cookie = \ub_api::get_auth_cookie();
		
		return \ub_api::searchRequest($cookie, $searchquery, $page);
		
	}
	
	/**
	 * Clean response
	 * If a response attribute is unknown from the description, we just ignore the attribute.
	 * If a response attribute is incorrect, invalid_response_exception is thrown.
	 * Note: this function is similar to validate parameters, however it is distinct because
	 * parameters validation must be distinct from cleaning return values.
	 *
	 * @param external_description $description description of the return values
	 * @param mixed $response the actual response
	 * @return mixed response with added defaults for optional items, invalid_response_exception thrown if any problem found
	 */
	public static function clean_returnvalue(\external_description $description, $response)
	{
		return $response;
	}
}
