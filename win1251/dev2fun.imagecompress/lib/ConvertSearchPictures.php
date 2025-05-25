<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.5
 * @since 0.11.5
 */

namespace Dev2fun\ImageCompress;

class ConvertSearchPictures
{
    /**
     * @param string $path
     * @param array $excludePaths
     * @return bool
     */
    public static function isExcluded(string $path, array $excludePaths): bool
    {
        return IO::isExcluded($path, $excludePaths);
    }

    /**
     * @param string $path
     * @return int
     */
    public static function getCountFiles(string $path): int
    {
        return IO::getCountFiles($path);
    }

    /**
     * @param string $dir
     * @param array $excludePaths
     * @param int $chunkFiles
     * @return array|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function scanFiles(string $dir, array $excludePaths = [], int $chunkFiles = 500): ?array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $extSupport = [
            'jpeg',
            'jpg',
            'png',
        ];
        $files = [];
        $lastFile = null;
        $cntProcess = 0;
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (self::isExcluded($file->getPathname(), $excludePaths)) {
                continue;
            }

            if ($file->isFile()) {
                if (!in_array($file->getExtension(), $extSupport)) {
                    continue;
                }

                $files[] = $file->getPathname();
                $cntProcess++;

                if (count($files) >= $chunkFiles) {
                    self::insertImage($files);
                    $files = [];
                }
            }

            $lastFile = $file->getPathname();
        }

        if ($files) {
            self::insertImage($files);
        }

        return [
            'cnt' => $cntProcess,
            'lastFile' => $lastFile,
        ];
    }

    /**
     * @param array $files
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function insertImage(array $files): void
    {
        $currentFiles = LazyConvert::findFiles($files);
        $rows = [];
        foreach ($files as $file) {
            $md5 = md5_file($file);
            if (empty($currentFiles[$md5])) {
                $fileUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
                $rows[] = [
                    'IMAGE_PATH' => $fileUrl,
                    'IMAGE_HASH' => $md5,
                    'IMAGE_IGNORE' => 'N',
                ];
            }
        }

        $sql = MySqlHelper::getInsertIgnoreMulti(
            ImageCompressImagesTable::getTableName(),
            $rows
        );

        \Bitrix\Main\Application::getInstance()
            ->getConnection()
            ->queryExecute($sql);
    }
}