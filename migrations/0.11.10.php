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

    DeleteDirFilesEx("/bitrix/js/{$curModuleName}/vue");
    CopyDirFiles(
        "{$_SERVER["DOCUMENT_ROOT"]}/bitrix/modules/{$curModuleName}/install/js/vue",
        "{$_SERVER["DOCUMENT_ROOT"]}/bitrix/js/{$curModuleName}/vue",
        true,
        true
    );

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.10 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
