<?php

const BASE_URL = 'https://biblioclub.ru';

class ub_api
{
	/**
	 * @var string
	 */
	public static $authurl = BASE_URL . '/index.php';
	
	/**
	 * @var string
	 */
	private static $searchurl = BASE_URL . '/services/service.php?page=all_search&m=SQuery&pjson&out=json';
	
	/**
	 * @var string
	 */
	private static $booksInfo = BASE_URL . '/services/service.php?page=books&m=GetShortInfo_S&pjson&out=json';
	
	/**
	 * @var string
	 */
	private static $bookFields = BASE_URL . '/services/service.php?page=books&m=GetFields_S&pjson&out=json';
	
	/**
	 * @var string
	 */
	private static $biblioDescs = BASE_URL . '/services/service.php?page=books&m=GetLibDescs&pjson&out=json';
	
	/**
	 * @var string
	 */
	private static $me = BASE_URL . '/services/users.php?users_action=check_auth';
	
	/**
	 * @var string
	 */
	private static $viewLinks = BASE_URL . '/services/service.php?page=sandbox&m=FormOutCodes&pjson&out=json';
	
	/**
	 * @var string
	 */
	private static $coverUrl = 'https://img.biblioclub.ru/sm_cover/';
	
	/**
	 * @var int
	 */
	private static $requestTimeout = 600;
	
	/**
	 * @var int
	 */
	private static $onPage = 30;
	
	public static function buildAuthParams() 
	{
		global $USER;
		$domain = get_config('block_biblioclub_ru', 'domain');
		$secretkey = get_config('block_biblioclub_ru', 'secretkey');
		$timestamp = time();
		$user_id = $USER->id;
		$login = $USER->username;
		$sign = md5($user_id . $secretkey . $timestamp);
		if (strlen($login) < 6) {
			$sub_id = $user_id + 10000;
			$login .= '_' . $sub_id;
		}
		$params = [
			'page' => 'main_ub_red',
			'action' => 'auth_for_org',
			'domain' => $domain,
			'user_id' => $user_id,
			'login' => $login,
			'time' => $timestamp,
			'sign' => $sign,
			'type' => 6, //FIXME!! проверить роль
			'first_name' => $USER->firstname,
			'last_name' => $USER->lastname,
			'utf' => 1,
		];
		if ($USER->middlename) {
			$params['parent_name'] = $USER->middlename;
		}
		
		return $params;
	}
	
	/**
	 * Получить куку авторизации для запросов
	 * @return string
	 * @throws \dml_exception
	 */
	public static function get_auth_cookie()
	{
		$now = new \DateTime("now", new \DateTimeZone('Europe/Moscow'));
		// проверим код в сессии
		if (isset($_SESSION['mod_biblioclubrubook_auth']) && !empty($_SESSION['mod_biblioclubrubook_auth'])) {
			// проверяем наличие срока
			if (isset($_SESSION['mod_biblioclubrubook_auth']['expires']) &&
				!empty($_SESSION['mod_biblioclubrubook_auth']['expires'])) {
				// проверяем срок жизни
				if ($_SESSION['mod_biblioclubrubook_auth']['expires'] > $now->format('Y-m-d H:i:s')) {
					// проверяем наличие куки
					if (isset($_SESSION['mod_biblioclubrubook_auth']['cookie']) &&
						!empty($_SESSION['mod_biblioclubrubook_auth']['cookie'])) {
						// возвращаем куку из сессии
						return $_SESSION['mod_biblioclubrubook_auth']['cookie'];
					}
				}
			}
		}
		// авторизуем юзера на сайте и получаем его куку
		$url = static::$authurl . '?' . http_build_query(static::buildAuthParams(), '', '&');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		$cookies = [];
		foreach ($matches[1] as $item) {
			parse_str($item, $cookie);
			if (isset($cookie['PHPSESSID']) && !empty($cookie['PHPSESSID'])) {
				// куча редиректов - читаем все куки
				$cookies[] = $cookie;
			}
			
		}
		
		// нужная кука где-то посередине))
		if (count($cookies) >= 2) {
			$authCookie = $cookies[1]['PHPSESSID'];
			if (!empty($authCookie)) {
				// срок сессии на библиоклубе 12 часов (по максимуму)
				// запишем куку в сессию юзеру, чтобы потом ее оттуда достать
				$expires = new \DateTime("now", new \DateTimeZone('Europe/Moscow'));
				$expires->modify("+6 hours");
				$_SESSION['mod_biblioclubrubook_auth'] = [
					'expires' => $expires->format('Y-m-d H:i:s'),
					'cookie' => $authCookie
				];
				return $authCookie;
			}
		}
		return null;
	}
	
	public static function curlRequest(string $url, string $cookie, $jsonQuery = null)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => static::$requestTimeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_CUSTOMREQUEST => (empty($jsonQuery)) ? 'GET' : 'POST',
			CURLOPT_POSTFIELDS => (empty($jsonQuery)) ? null : 'p=' . json_encode($jsonQuery),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded',
				'Cookie: PHPSESSID=' . $cookie
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		if (empty($response)) return [];
		if (is_numeric($response)) return ['data' => $response];
		if (trim($response) === 'false') return ['data' => trim($response)];
		return json_decode($response, true);
		
	}
	
	public static function loadBooksInfo(array $booksWithIds, $cookie): array
	{
		$booksInfo = static::curlRequest(static::$booksInfo, $cookie, [
			'ids' => array_column($booksWithIds, 'book_id')
		]);
		
		// подгружаем библиографическое описание
		
		$libDescs = static::curlRequest(static::$biblioDescs, $cookie, array_column($booksWithIds, 'book_id'));
		
		foreach ($booksInfo as $i => $bookInfo) {
			foreach ($booksWithIds as $item) {
				if ($item['book_id'] == $bookInfo['id']) {
					$booksInfo[$i]['cname'] = $item['cname'];
					$booksInfo[$i]['cover'] = static::$coverUrl . $bookInfo['pic'];
					$booksInfo[$i]['biblio_record'] = $libDescs[$item['book_id']];
					$booksInfo[$i]['biblio'] = $libDescs[$item['book_id']];
				}
			}
		}
		
		return $booksInfo;
	}
	
	public static function searchRequest(string $cookie, string $query, int $page = 0)
	{
		
		$meta = [
			'currentPage' => 0,
			'pageCount' => 0
		];
		
		$result = ['publications' => [], '_meta' => $meta];
		
		if (empty($cookie) || empty($query)) return $result;
		
		$jsonQuery = [
			'psev' => 'book_names'
		];
		if (is_numeric($query)) {
			// ищем по book_id коду
			$jsonQuery['q'] = sprintf(
				"SELECT book_id, obj, cname FROM book_names WHERE
                       book_id = %d OR obj = %d AND moderating = 0 LIMIT 2 OPTION max_matches=900000",
				intval($query), intval($query));
		} else {
			// ищем по названию 
			$jsonQuery['q'] = sprintf(
				"SELECT book_id, obj, cname FROM book_names WHERE MATCH('@(cname) *%s*') AND moderating = 0 
											LIMIT 3000 OPTION max_matches=900000", trim($query));
		}
		
		$res = static::curlRequest(static::$searchurl, $cookie, $jsonQuery);
		if (empty($res)) return $result;
		
		$result['_meta']['pageCount'] = ceil(count($res) / static::$onPage);
		
		if (count($res) <= static::$onPage) {
			$result['publications'] = static::loadBooksInfo($res, $cookie);
			$result['_meta']['currentPage'] = 1;
			return $result;
		}
		
		if ($page == 0 || $page == 1) {
			$result['publications'] = array_slice(static::loadBooksInfo($res, $cookie), 0, static::$onPage);
			$result['_meta']['currentPage'] = 1;
			return $result;
		}
		
		$result['publications'] = array_slice(static::loadBooksInfo($res, $cookie),
			($page - 1) * static::$onPage, static::$onPage);
		$result['_meta']['currentPage'] = $page;
		
		return $result;
	}
	
	public static function getBookFields(string $cookie, int $bookId, array $fields)
	{
		if (empty($cookie) || empty($bookId) || empty($fields)) return null;
		$bookInfo = static::curlRequest(static::$bookFields, $cookie, [
			'cmplx_name' => 1,
			'id' => $bookId,
			'fields' => $fields
		]);
		
		if (empty($bookInfo) || (isset($bookInfo['data']) && $bookInfo['data'] === 'false')) return null;
		
		return $bookInfo;
		
	}
	
	public static function getUserId(string $cookie)
	{
		if (empty($cookie)) return null;
		$res = static::curlRequest(self::$me, $cookie);
		if (empty($res) || empty($res['data'])) return null;
		return $res['data'];
		
	}
	
	
	public static function getLinks(string $cookie, int $bookId, string $page)
	{
		
		if (empty($cookie) || empty($bookId)) return null;
		// проверим страницы
		$pagesArray = [];
		$bookPages = static::getBookFields($cookie, $bookId, ['pages']);
		if (empty($bookPages) || !count($bookPages)) return null;
		$totalBookPages = intval($bookPages[0]['pages']);
		
		if (empty($page)) {
			// если пусто - подгружаем страницы книги
			$pagesArray = [
				'from' => 1,
				'to' => $totalBookPages
			];
		}
		
		if (is_numeric($page) && $page != 0) {
			// судя по всему юзер хочет посмотреть конкретную страницу
			$page = intval($page);
			if ($page > $totalBookPages) {
				$page = $totalBookPages;
			}
			
			$pagesArray = [
				'from' => $page,
				'to' => $page
			];
		}
		
		if (mb_strpos($page, '-') !== false) {
			// пользователь указал диапазон страниц
			$pages = explode('-', $page);
			if (count($pages) != 2) {
				// криво задан диапазон страниц, показываем всю книгу
				$pagesArray = [
					'from' => 1,
					'to' => $totalBookPages
				];
			} else {
				if (is_numeric($pages[0]) && is_numeric($pages[1])) {
					// здесь - все правильно показываем только диапазон страниц
					$pagesArray = [
						'from' => intval($pages[0]),
						'to' => intval($pages[1])
					];
				} else {
					// снова как-то не так указан диапазон
					$pagesArray = [
						'from' => 1,
						'to' => $totalBookPages
					];
				}
			}
		}
		
		$userDomain = get_config('block_biblioclub_ru', 'domain');
		$uid = static::getUserId($cookie);
		if (empty($pagesArray)) return null;
		
		$paramsArray = array_merge(['id' => $bookId, 'type' => 'bk_v',
			'uid' => $uid, 'domen' => $userDomain], $pagesArray);
		
		$links = static::curlRequest(static::$viewLinks, $cookie, [
			'links' => [$paramsArray]
		]);
		
		if (empty($links) || !count($links)) return null;
		
		return $links[0]['links'];
	}
	
}

