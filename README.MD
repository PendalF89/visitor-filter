Visitor Filter
================
Фильтр посетителей по определённым параметрам

Параметры, по которым возможен фильтр
------------
* Страна
* Язык
* IP адрес
* Был ли пользователь на сайте
* Адрес, с которого пришёл пользователь

Установка
------------
Установка через [composer](http://getcomposer.org/download/).

Запустите команду

```
php composer.phar require --prefer-dist pendalf89/visitor-filter "*"
```

или добавить строку в composer.json

```
"pendalf89/visitor-filter": "*"
```


Использование
------------

```php
$config = [
    // Разрешено для всех посетителей. Если true, то остальные правила игнорируются.
    'allowToAll' => false,

    // Запрещено для всех посетителей. Если true, то остальные правила игнорируются.
    'disallowToAll' => false,

    // Список запрещённых языков (iso-код, например "ru-RU", "en-US" и т.д.)
    'disallowedLanguages' => ['en-US'],

    // Список запрещённых стран (iso-код, например "US", "RU" и т.д.)
    'disallowedCountries' => ['US'],

    // Список запрещённых url'ов, с которых пришёл пользователь.
    // Допускается писать не весь url, а только часть.
    // Поиск производится по вхождению.
    // Например, "google.com", "yandex.ru" и т.д.
    'disallowedReferers' => ['google.com'],

    // Список запрещённых ip адресов
    'disallowedIpAddresses' => [],

    // Запретить, если пользователь уже был на сайте
    'disallowIfVisitorWasHere' => false,
];

$visitorFilter = new \pendalf89\visitor_filter\VisitorFilter($config);
if ($visitorFilter->isAllow()) {
    // ваш код
}
```

Для определения страны используется база IP адресов [maxmind](http://www.maxmind.com).