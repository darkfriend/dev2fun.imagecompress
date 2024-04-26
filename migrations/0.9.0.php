<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 26.04.2024
 * Time: 22:11
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$curModuleName = 'dev2fun.imagecompress';
\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule($curModuleName);

\Bitrix\Main\Loader::registerAutoLoadClasses(
    $curModuleName,
    [
        'Dev2funImageCompress' => 'include.php',
        "Dev2fun\ImageCompress\Cache" => 'lib/Cache.php',
    ]
);

try {
    // add agent
    $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 120, 'FULL');
    $agentId = CAgent::AddAgent(
        \Dev2fun\ImageCompress\Cache::class . '::agentRun();',
        $curModuleName,
        'Y',
        120,
        '',
        'N',
        $startTime,
        100,
        false,
        false
    );
    if (!$agentId) {
        throw new Exception('Error when add agent for cache-delete');
    }
    \Bitrix\Main\Config\Option::set($curModuleName, 'cache_delete_agent', $agentId);

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.9.0 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
