<?php
/**
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 12.05.2025
 * Time: 00:32
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$curModuleName = 'dev2fun.imagecompress';
\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule($curModuleName);

\Bitrix\Main\Loader::registerAutoLoadClasses(
    $curModuleName,
    [
        'Dev2funImageCompress' => 'include.php',
    ]
);

try {

    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->registerEventHandler(
        "main",
        "OnFileDelete",
        $curModuleName,
        "Dev2fun\\ImageCompress\\Convert",
        "ConvertImageOnFileDeleteEvent"
    );

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.4 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
