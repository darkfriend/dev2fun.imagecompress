<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 13.10.2020
 * Time: 2:12
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$curModuleName = 'dev2fun.imagecompress';
\Bitrix\Main\Loader::includeModule($curModuleName);
\Bitrix\Main\Loader::registerAutoLoadClasses(
    $curModuleName,
    [
        'Dev2funImageCompress' => 'include.php',
    ]
);

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->registerEventHandler("main", "OnAfterResizeImage", $curModuleName, "Dev2fun\\ImageCompress\\Convert", "CompressImageCacheOnConvertEvent");

\Dev2funImageCompress::ShowThanksNotice();

die("0.5.3 - Success");