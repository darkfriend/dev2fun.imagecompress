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

    $browsersDefault = [
        'chrome',
        'opera',
        'other',
    ];
    \Bitrix\Main\Config\Option::set(
        $curModuleName,
        'browsers_support',
        json_encode($browsersDefault)
    );

    \Bitrix\Main\Config\Option::set($curModuleName, 'header_accept', 'Y');

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.8 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
