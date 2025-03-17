<?php
/**
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 17.03.2025
 * Time: 19:34
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
    ]
);

try {

    /** @var Main\DB\MysqlCommonConnection $connection */
    $connection = \Dev2fun\ImageCompress\ImageCompressImagesTable::getEntity()->getConnection();

    $isTableExists = $connection->isTableExists(\Dev2fun\ImageCompress\ImageCompressImagesTable::getTableName());
    if ($isTableExists && !$connection->isIndexExists(\Dev2fun\ImageCompress\ImageCompressImagesTable::getTableName(), ['IMAGE_HASH', 'IMAGE_IGNORE'])) {
        $connection->createIndex(
            \Dev2fun\ImageCompress\ImageCompressImagesTable::getTableName(),
            'indx_image_hash',
            ['IMAGE_HASH', 'IMAGE_IGNORE']
        );
    }

    $isTableExists = $connection->isTableExists(\Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::getTableName());
    if ($isTableExists && !$connection->isIndexExists(\Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::getTableName(), ['ORIGINAL_IMAGE_HASH'])) {
        $connection->createIndex(
            \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::getTableName(),
            'indx_original_image_hash',
            ['ORIGINAL_IMAGE_HASH']
        );
    }

    \Dev2funImageCompress::ShowThanksNotice();

    die("0.11.2 - Success");

} catch (Throwable $e) {
    ShowError($e->getMessage());
}
