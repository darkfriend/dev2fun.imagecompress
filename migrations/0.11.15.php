<?php
/**
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 12.04.202
 * Time: 21:52
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
    CopyDirFiles(
        "{$_SERVER["DOCUMENT_ROOT"]}/bitrix/modules/{$curModuleName}/install/css",
        "{$_SERVER["DOCUMENT_ROOT"]}/bitrix/css/{$curModuleName}",
        true,
        true
    );

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.15 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
