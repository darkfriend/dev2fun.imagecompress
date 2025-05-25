<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.5
 * @since 0.11.0
 */

namespace Dev2fun\ImageCompress;

class IO
{
    /**
     * Проверяет директорию на пустоту
     * @param string $path
     * @return bool
     */
    public static function isEmptyDir(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        $path = realpath($path);
        if ($path) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
                if ($object->isFile()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param string $path
     * @param array $excludePaths
     * @return bool
     */
    public static function isExcluded(string $path, array $excludePaths): bool
    {
        foreach ($excludePaths as $excludePath) {
            if (strpos($path, $excludePath) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $path
     * @return int
     */
    public static function getCountFiles(string $path, array $excludePaths = []): int
    {
        $count = 0;
        $path = realpath($path);
        if ($path && file_exists($path)) {
            /** @var \RecursiveDirectoryIterator $object */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
                if ($object->isFile() && !self::isExcluded($object->getPathname(), $excludePaths)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}