<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 12.01.2024
 * Time: 0:54
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$curModuleName = 'dev2fun.imagecompress';
\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule($curModuleName);

\Bitrix\Main\Loader::registerAutoLoadClasses(
    $curModuleName,
    [
        'Dev2funImageCompress' => 'include.php',
        'Dev2fun\ImageCompress\ImageCompressImagesTable' => 'classes/general/ImageCompressImagesTable.php',
        'Dev2fun\ImageCompress\ImageCompressImagesConvertedTable' => 'classes/general/ImageCompressImagesConvertedTable.php',
        'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable' => 'classes/general/ImageCompressImagesToConvertedTable.php',

        'Dev2fun\ImageCompress\MySqlHelper' => 'classes/general/MySqlHelper.php',
        "Dev2fun\ImageCompress\LazyConvert" => 'lib/LazyConvert.php',
    ]
);

use \Dev2fun\ImageCompress\ImageCompressImagesTable;
use \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable;
use \Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable;

try {
    $connection = \Bitrix\Main\Application::getInstance()->getConnection();
    $instance = \Dev2fun\ImageCompress\Compress::getInstance();

    // add tables
    if (!$connection->isTableExists(ImageCompressImagesTable::getTableName())) {
        ImageCompressImagesTable::createTable();
    }
    if (!$connection->isTableExists(ImageCompressImagesConvertedTable::getTableName())) {
        ImageCompressImagesConvertedTable::createTable();
    }
    if (!$connection->isTableExists(ImageCompressImagesToConvertedTable::getTableName())) {
        ImageCompressImagesToConvertedTable::createTable();
    }

    // copy files
    CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
    CopyDirFiles(__DIR__ . "/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);

    // add options
    $options = \Bitrix\Main\Config\Option::getForModule(Dev2funImageCompress::MODULE_ID, false);
    $sites = Dev2funImageCompress::getSites();
    $excludeOptions = [
        'convert_agent',
        'cnt_step',
    ];
    foreach ($options as $key => $value) {
        if (in_array($key, $excludeOptions)) {
            continue;
        }
        foreach ($sites as $site) {
            \Bitrix\Main\Config\Option::set(
                Dev2funImageCompress::MODULE_ID,
                $key,
                $value,
                $site['ID']
            );
        }
    }

    // add agent
    $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 60, 'FULL');
    $agentId = CAgent::AddAgent(
        \Dev2fun\ImageCompress\LazyConvert::class . '::agentRun();',
        Dev2funImageCompress::MODULE_ID,
        'Y',
        60,
        '',
        'Y',
        $startTime,
        100,
        false,
        false
    );
    if (!$agentId) {
        throw new Exception('Error when add agent');
    }

    \Bitrix\Main\Config\Option::set(Dev2funImageCompress::MODULE_ID, 'convert_agent', $agentId);

    $eventManager = \Bitrix\Main\EventManager::getInstance();
    // remove events
    $eventManager->unRegisterEventHandler('main', 'OnGetFileSRC', Dev2funImageCompress::MODULE_ID);
    $eventManager->unRegisterEventHandler('main', 'OnAfterResizeImage', Dev2funImageCompress::MODULE_ID);
    $eventManager->unRegisterEventHandler('main', 'OnEndBufferContent', Dev2funImageCompress::MODULE_ID);

    // register events
    $eventManager->registerEventHandler("main", "OnGetFileSRC", Dev2funImageCompress::MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "CompressImageOnConvertEvent", 999);
    $eventManager->registerEventHandler("main", "OnAfterResizeImage", Dev2funImageCompress::MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "CompressImageCacheOnConvertEvent", 999);

    $eventManager->registerEventHandler("main", "OnEndBufferContent", Dev2funImageCompress::MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "PostConverterEvent", 999);

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.8.0 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
//    throw $e;
}
