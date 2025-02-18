<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.0
 * @since 0.11.0
 */

namespace Dev2fun\ImageCompress;

class CacheCleaner
{
    /**
     * Очистка всего кэша на событии
     * @see Convert::CleanCacheEvent
     * @return void
     */
    public static function cleanOnEvent(): void
    {
        static::clearingConvertResizeCache();
    }

    /**
     * Процесс очистки всего кэша
     * @return void
     */
    public static function clearingConvertResizeCache(): void
    {
        static::cleanResizeCacheDb();
        static::cleanResizeCacheDirectory();
//        $rows = ImageCompressImagesConvertedTable::query()
//            ->setSelect([
//                'ID',
//                'IMAGE_PATH',
//            ])
//            ->whereLike('IMAGE_PATH', '%resize_cache%')
//            ->setLimit($requestBody["countPage"] ?? 500)
//            ->fetchAll();
//
//        if (!$rows) {
//            return;
//        }
//
//        foreach ($rows as $row) {
//            ImageCompressImagesConvertedTable::delete($row['ID']);
//            $path = self::getAbsolutePath($row['IMAGE_PATH']);
//            if (is_file($path)) {
//                @unlink($path);
//                if (self::checkOnEmptyDir(dirname($path))) {
//                    @rmdir($path);
//                }
//            }
//        }
    }

    /**
     * Возвращает массив путей до resize_cache
     * @return string[]
     */
    public static function getPaths(): array
    {
        return [
            'webp' => Convert::getAbsolutePath(
                Convert::getConvertedPath('resize_cache', 'webp')
            ),
            'avif' => Convert::getAbsolutePath(
                Convert::getConvertedPath('resize_cache', 'avif')
            ),
        ];
    }

    /**
     * Очищает все папки с resize_cache
     * @return void
     */
    public static function cleanResizeCacheDirectory(): void
    {
        foreach (static::getPaths() as $path) {
            DeleteDirFilesEx($path);
        }
    }

    /**
     * Очищает записи resize_cache в бд
     * @return void
     */
    public static function cleanResizeCacheDb(): void
    {
        try {
            static::cleanSourceImageResizeCacheDb();
            static::cleanConvertedImageResizeCacheDb();
        } catch (\Exception $e) {
            CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'CacheCleaner',
                'MODULE_ID' => \Dev2funImageCompress::MODULE_ID,
                'DESCRIPTION' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Очищает записи resize_cache в ImageCompressImagesConvertedTable
     * @return void
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public static function cleanConvertedImageResizeCacheDb(): void
    {
        $convertedImageTable = ImageCompressImagesConvertedTable::getTableName();
        $moduleName = \Dev2funImageCompress::MODULE_ID;
        $sql = <<<SQL
            DELETE 
            FROM {$convertedImageTable} 
            WHERE IMAGE_PATH LIKE '/upload/{$moduleName}/webp/resize_cache%' 
               OR IMAGE_PATH LIKE '/upload/{$moduleName}/avif/resize_cache%';
SQL;

        $con = \Bitrix\Main\Application::getConnection();
        $con->queryExecute($sql);
    }

    /**
     * Очищает записи resize_cache в ImageCompressImagesTable
     * @return void
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public static function cleanSourceImageResizeCacheDb(): void
    {
        $sourceImageTable = ImageCompressImagesTable::getTableName();
        $sql = <<<SQL
            DELETE 
            FROM {$sourceImageTable} 
            WHERE IMAGE_PATH LIKE '/upload/resize_cache%';
SQL;

        $con = \Bitrix\Main\Application::getConnection();
        $con->queryExecute($sql);
    }
}