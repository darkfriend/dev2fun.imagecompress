<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 23.07.2024
 * Time: 21:03
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
    $sites = [];
    $rsSites = CSite::GetList();
    while ($site = $rsSites->Fetch()) {
        $sites[] = $site;
    }

    foreach ($sites as $site) {
        $pages = \Bitrix\Main\Config\Option::get(\Dev2funImageCompress::MODULE_ID, 'exclude_pages', '', $site['ID']);
        if ($pages) {
            $pages = \json_decode($pages, true);
        } else {
            $pages = [];
        }
        if (!$pages) {
            continue;
        }
        $pages = array_filter($pages, function($page) {
            return $page !== '#(\/bitrix\/.*)#';
        });
        \Bitrix\Main\Config\Option::set(
            \Dev2funImageCompress::MODULE_ID,
            'exclude_pages',
            \json_encode(\array_values($pages)),
            $site['ID']
        );
    }

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.10.0 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
