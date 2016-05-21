<?php

namespace pendalf89\visitor_filter;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

/**
 * Class VisitorInfo
 *
 * Класс предназначен для получения информации о посетителе:
 * - iso код страны
 * - ip адрес
 * - язык пользователя
 * - был ли он на сайте
 * - адрес, с которого пришёл посетитель
 *
 * Если какой-то из параметров опрделить не удалось, вместо него запишется null.
 *
 * Пример использования:
 *
 *  $visitorInfo = new \pendalf89\visitor_filter\VisitorInfo();
 *  echo $visitorInfo->getIp();
 *	echo $visitorInfo->getCountryIsoCode();
 *	echo $visitorInfo->getLanguage();
 *	echo $visitorInfo->getHttpReferer();
 *	var_dump($visitorInfo->isVisitorWasHere());
 *
 *
 * This product includes GeoLite2 data created by MaxMind, available from http://www.maxmind.com
 *
 * @package pendalf89\visitor_filter
 */
class VisitorInfo
{
	/**
	 * @var string IP адрес
	 */
	protected $ip;

	/**
	 * @var string страна пользователя
	 */
	protected $countryIsoCode;

	/**
	 * @var string язык пользователя
	 */
	protected $language;

	/**
	 * @var string адрес, с которого пришёл пользователь
	 */
	protected $httpReferer;

	/**
	 * @var string файл с базой maxmind
	 * @see https://dev.maxmind.com/geoip/geoip2/geolite2/ (см. GeoLite2 Country)
	 */
	protected $geoIpDatabase = __DIR__ . '/db/GeoLite2-Country.mmdb';

	/**
	 * @var string имя файла cookie
	 */
	protected $cookieName = '_vf_visitor_was_here';

	/**
	 * VisitorInfo constructor.
	 */
	public function __construct()
	{
		$this->detectIp();
		$this->detectCountry();
		$this->detectLanguage();
		$this->detectHttpReferer();
	}

	/**
	 * IP адрес
	 *
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}

	/**
	 * Страна пользователя
	 *
	 * @return string
	 */
	public function getCountryIsoCode()
	{
		return $this->countryIsoCode;
	}

	/**
	 * Возвращает язык пользователя
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Возвращает адрес, с которого пришёл пользователь
	 *
	 * @return string
	 */
	public function getHttpReferer()
	{
		return $this->httpReferer;
	}

	/**
	 * Определяет был ли пользователь на сайте
	 *
	 * @return string
	 */
	public function isVisitorWasHere()
	{
		return isset($_COOKIE[$this->cookieName]) && $_COOKIE[$this->cookieName];
	}

	/**
	 * Определяет IP пользователя
	 */
	protected function detectIp()
	{
		if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		} elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		} elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}

		if (substr_count($ip, ',')) {
			$ip = trim(explode(',', $ip)[0]);
		}

		$this->ip = $ip;
	}

	/**
	 * Определяет страну пользователя
	 */
	protected function detectCountry()
	{
		$reader = new Reader($this->geoIpDatabase);

		try {
			$record = $reader->country($this->getIp());
			$this->countryIsoCode = $record->country->isoCode;
		} catch (AddressNotFoundException $e) {
			$this->countryIsoCode = null;
		}
	}

	/**
	 * Определяет язык пользователя
	 */
	protected function detectLanguage()
	{
		$this->language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
			? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5)
			: null;
	}

	/**
	 * Определяет адрес, с которого пришёл пользователь
	 */
	protected function detectHttpReferer()
	{
		$this->httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
	}

	/**
	 * Записывает в cookie информацию о том, что пользователь был на сайте.
	 */
	public function touchCookie()
	{
		setcookie($this->cookieName, 1);
	}

	/**
	 * Удаляет cookie с информацией о посещении
	 */
	public function removeCookie()
	{
		setcookie($this->cookieName, null, -1);
	}
}