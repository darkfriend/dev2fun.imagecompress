#!/usr/bin/php
<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.9.0
 * @since 0.9.0
 * @example /path_to_module/console/cache-delayed-delete.php -l=1000
 * @example /2 * * * * php -f /path_to_module/console/cache-delayed-delete.php -l=1000
 */

set_time_limit(3600);
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../../../';

define("NOT_CHECK_PERMISSIONS", true);
//define("BX_UTF", true);
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
//define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);
define("BX_BUFFER_USED", true);
error_reporting(E_ALL | E_ERROR | E_PARSE);
ini_set("display_errors", "On");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

//error_reporting(E_ALL|E_ERROR|E_PARSE);
//ini_set("display_errors","On");

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('dev2fun.imagecompress');

$params = getopt('l::');
if (!empty($params['l'])) {
    $limit = (int)$params['l'];
} else {
    $limit = \Bitrix\Main\Config\Option::get('dev2fun.imagecompress', 'cache_delete_length', 1000);
}

if (empty($limit)) {
    $limit = 1000;
}

\Bitrix\Main\Data\CacheEngineFiles::delayedDelete($limit);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");