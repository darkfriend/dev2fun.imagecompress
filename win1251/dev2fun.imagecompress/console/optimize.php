#!/usr/bin/php
<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.3.0
 * @copyright dev2fun
 */
set_time_limit(3600);
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . '/../../../../';

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

$params = getopt('c::', ['limit::']);

$limit = \Bitrix\Main\Config\Option::get('dev2fun.imagecompress', "cnt_step", 30);
if (isset($params['limit'])) {
    $limit = intval($params['limit']);
}

$noCompress = true;
if (isset($params['c'])) {
    $noCompress = $params['c'] == 'N' ? false : true;
}

$filterList = [
    //	'COMRESSED' => 'N',
    '@CONTENT_TYPE' => [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ],
];
if ($noCompress) {
    $filterList['COMRESSED'] = 'N';
}

$rsRes = \Dev2fun\ImageCompress\Compress::getInstance()->getFileList(
    [],
    $filterList,
    $limit
);

//$pageSize = \Bitrix\Main\Config\Option::get('dev2fun.imagecompress', "cnt_step", 30);
$rsRes->NavStart($limit, false);

$navPageCount = $rsRes->NavPageCount;
$currentPageSize = $rsRes->NavPageSize;
if (!$navPageCount) $navPageCount = 1;
$stepOnPage = 1;

while ($arFile = $rsRes->NavNext(true)) {
    $strFilePath = \CFile::GetPath($arFile["ID"]);
    $progressValue = $stepOnPage / $currentPageSize * 100;
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $strFilePath)) {
        \Dev2fun\ImageCompress\Compress::getInstance()->addCompressTable($arFile['ID'], [
            'FILE_ID' => $arFile['ID'],
            'SIZE_BEFORE' => 0,
            'SIZE_AFTER' => 0,
        ]);
        echo "Error. Image #{$arFile['ID']} is not compressed! File not found!" . PHP_EOL;
        echo "Progress: {$progressValue}%" . PHP_EOL;
        $stepOnPage++;
        continue;
    }
    echo "Success. Image #{$arFile['ID']} compressed." . PHP_EOL;
    echo "Progress: {$progressValue}%" . PHP_EOL;
    $recCompress = \Dev2fun\ImageCompress\Compress::getInstance()->compressImageByID($arFile['ID']);
    $stepOnPage++;
}