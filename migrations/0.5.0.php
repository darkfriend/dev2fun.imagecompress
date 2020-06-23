<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 23.06.2020
 * Time: 4:54
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
$eventManager->registerEventHandler("main", "OnGetFileSRC", $curModuleName, "Dev2fun\\ImageCompress\\Convert", "CompressImageOnConvertEvent");

\Dev2funImageCompress::ShowThanksNotice();

die("0.5.0 - Success");