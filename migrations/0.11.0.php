<?php
/**
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 19.02.2025
 * Time: 02:41
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

    $cntWrongRelations = \Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable::getCountWrongRelations();
    if ($cntWrongRelations) {
        $cntPerStep = 500;
        $steps = ceil($cntWrongRelations / $cntPerStep);
        for ($i = 1; $i <= $steps; $i++) {
            \Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable::removeWrongRelations($cntPerStep);
        }
    }

    \Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable::addForeignKey();


    CopyDirFiles("{$_SERVER["DOCUMENT_ROOT"]}/bitrix/modules/{$curModuleName}/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
    CopyDirFiles("{$_SERVER["DOCUMENT_ROOT"]}/bitrix/modules/{$curModuleName}/install/js/vue", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/{$curModuleName}/vue", true, true);

    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->registerEventHandler("main", "OnPageStart", $curModuleName, "Dev2fun\\ImageCompress\\Convert", "CleanCacheEvent", 999);

    $cntRows = \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::query()
        ->whereLike('IMAGE_PATH', '/upload/resize_cache%')
        ->queryCountTotal();

    if ($cntRows) {
        \CAdminNotify::Add([
            'MESSAGE' => "Вам необходимо сделать перенос сконвертированных файлов в новое место расположение, <a href='/bitrix/admin/dev2fun_imagecompress_convert_move.php?lang=ru'>ссылка</a>",
            'TAG' => $curModuleName . '_convert_move',
            'MODULE_ID' => $curModuleName,
            'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
        ]);
    }

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.0 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
