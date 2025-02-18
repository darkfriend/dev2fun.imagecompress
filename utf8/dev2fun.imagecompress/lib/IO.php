<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.0
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
    public static function checkOnEmptyDir(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }
        $handle = opendir($path);
        while ($entry = readdir($handle)) {
            if ($entry !== '.' || $entry !== '..') {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }
}