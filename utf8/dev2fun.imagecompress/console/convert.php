#!/usr/bin/php
<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 * @since 0.7.4
 * @example ./convert.php -l=10
 */

set_time_limit(3600);
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../../../';

define("NOT_CHECK_PERMISSIONS", true);
//define("BX_UTF", true);
define("NO_KEEP_STATISTIC", true);
//define("NOT_CHECK_PERMISSIONS", true);
define("BX_BUFFER_USED", true);
error_reporting(E_ALL | E_ERROR | E_PARSE);
ini_set("display_errors", "On");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

//error_reporting(E_ALL|E_ERROR|E_PARSE);
//ini_set("display_errors","On");

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('dev2fun.imagecompress');

$params = getopt('l::');
if (!empty($params['l'])) {
    $limit = (int)$params['l'];
} else {
    $limit = 500;
}

$connection = \Bitrix\Main\Application::getConnection();
$sqlHelper = $connection->getSqlHelper();
$cnt = $connection->queryScalar(
    "SELECT COUNT(*) FROM b_file WHERE CONTENT_TYPE IN ('image/jpeg', 'image/png', 'image/gif')"
);

echo "Found {$cnt} files" . PHP_EOL;

$convert = \Dev2fun\ImageCompress\Convert::getInstance();

if (!$convert->enable) {
    echo "Converter is not active. Please activate function convert in module settings." . PHP_EOL;
    die();
}

echo "Start progress" . PHP_EOL;

$lastId = null;
$upload_dir = \Bitrix\Main\Config\Option::get('main', 'upload_dir', 'upload');

//$files = [];
$stepOnPage = 0;
for ($i=1; $i<=$cnt; $i+=$limit) {
    $sql = "SELECT f.* FROM b_file f WHERE f.CONTENT_TYPE IN ('image/jpeg', 'image/png', 'image/gif')";
    if ($lastId) {
        $sql .= " AND f.ID>{$sqlHelper->forSql($lastId)}";
    }
    $sql .= " ORDER BY f.ID ASC LIMIT {$sqlHelper->forSql($limit)}";
    $recordset = $connection->query($sql);
    while ($arFile = $recordset->fetch()) {
        $stepOnPage++;
        $progressValue = round($stepOnPage / $cnt * 100, 2);
        $pathFile = "{$_SERVER["DOCUMENT_ROOT"]}/{$upload_dir}/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        if (!is_file($pathFile)) {
            echo "Warning: file #{$arFile['ID']} not fount. Continue." . PHP_EOL;
            continue;
        }
        echo "Progress: convert image #{$arFile['ID']}" . PHP_EOL;

        if(!$convert->process($arFile) && $convert->LAST_ERROR) {
            echo "ERROR! {$convert->LAST_ERROR}" . PHP_EOL;
        } else {
            echo "Success. Image #{$arFile['ID']} converted." . PHP_EOL;
        }

        $lastId = $arFile['ID'];
        echo "Progress: {$progressValue}%" . PHP_EOL;
    }
}

echo "Finish progress" . PHP_EOL;
