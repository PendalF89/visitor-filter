<?php

namespace pendalf89\visitor_filter;

/**
 * Class VisitorFilter
 *
 * Класс предназначен для разрешения/запрета чего-либо на основе данных посетителя.
 * Подробнее о данных см. в классе VisitorInfo
 *
 * Пример использования:
 *
 *  $config = [
 *	    'allowToAll' => false,
 *	    'disallowedLanguages' => ['en-US'],
 *	    'disallowedCountries' => ['US'],
 *	    'disallowedReferers' => ['google.com'],
 *	    'disallowedIpAddresses' => [],
 *	    'disallowIfVisitorWasHere' => false,
 *	];
 *	$visitorInfo = new \pendalf89\visitor_filter\VisitorFilter($config);
 *	var_dump($visitorInfo->isAllow());
 *
 * @package pendalf89\visitor_filter
 */
class VisitorFilter
{
	/**
	 * @var bool разрешено для всех посетителей. Если true, то остальные правила игнорируются.
	 */
	public $allowToAll = false;
	
	/**
	 * @var array список запрещённых стран (iso-код, например "US", "RU" и т.д.)
	 */
	public $disallowedCountries = [];

	/**
	 * @var array список запрещённых языков (iso-код, например "ru-RU", "en-US" и т.д.)
	 */
	public $disallowedLanguages = [];

	/**
	 * @var array список запрещённых url'ов, с которых пришёл пользователь.
	 * Допускается писать не весь url, а только часть.
	 * Поиск производится по вхождению.
	 * 
	 * Например, "google.com", "yandex.ru" и т.д.
	 */
	public $disallowedReferers = [];

	/**
	 * @var array список запрещённых ip адресов
	 */
	public $disallowedIpAddresses = [];

	/**
	 * @var bool запретить, если пользователь уже был на сайте
	 */
	public $disallowIfVisitorWasHere = false;

	/**
	 * @var VisitorInfo
	 */
	public $visitorInfo;

	/**
	 * VisitorFilter constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->visitorInfo = new VisitorInfo();
		
		foreach ($config as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Применяет все фильтры и выдаёт результат: разрешено или запрещено.
	 *
	 * @return bool
	 */
	public function isAllow()
	{
		$isAllow = true;

		if ($this->allowToAll) {
			return $isAllow;
		}

		$visitorInfo     = $this->visitorInfo;
		$visitorCountry  = $visitorInfo->getCountryIsoCode();
		$visitorIp       = $visitorInfo->getIp();
		$visitorLanguage = $visitorInfo->getLanguage();
		$visitorReferer  = $visitorInfo->getHttpReferer();
		
		if (!is_null($visitorCountry)) {
			foreach ($this->disallowedCountries as $disallowedCountry) {
				if ($disallowedCountry === $visitorCountry) {
					$isAllow = false;
					break;
				}
			}
		}

		if (!is_null($visitorIp)) {
			foreach ($this->disallowedIpAddresses as $disallowedIpAddress) {
				if ($disallowedIpAddress === $visitorIp) {
					$isAllow = false;
					break;
				}
			}
		}

		if (!is_null($visitorLanguage)) {
			foreach ($this->disallowedLanguages as $disallowedLanguage) {
				if ($disallowedLanguage === $visitorLanguage) {
					$isAllow = false;
					break;
				}
			}
		}

		if (!is_null($visitorReferer)) {
			foreach ($this->disallowedReferers as $disallowedReferer) {
				if (substr_count($visitorReferer, $disallowedReferer)) {
					$isAllow = false;
					break;
				}
			}
		}
		
		if ($this->disallowIfVisitorWasHere) {
			if ($visitorInfo->isVisitorWasHere()) {
				$isAllow = false;
			}
			$visitorInfo->touchCookie();
		}
		
		return $isAllow;
	}
}