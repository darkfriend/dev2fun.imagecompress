<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class Check
{
    public static $optiClasses = [
        'jpegoptim' => '\Dev2fun\ImageCompress\Jpegoptim',
        'optipng' => '\Dev2fun\ImageCompress\Optipng',
        'ps2pdf' => '\Dev2fun\ImageCompress\Ps2Pdf',
//        'webp' => '\Dev2fun\ImageCompress\Webp',
        'cwebp' => '\Dev2fun\ImageCompress\Webp',
//        'gif' => '\Dev2fun\ImageCompress\Gif',
        'gifsicle' => '\Dev2fun\ImageCompress\Gif',
//        'svg' => '\Dev2fun\ImageCompress\Svg',
        'svgo' => '\Dev2fun\ImageCompress\Svg',
    ];

    public static $lastError;

    /**
     * @param string $algorithm
     * @return bool
     * @deprecated
     * @uses Check::isOptim()
     */
    public static function isPNGOptim($algorithm)
    {
        if (!$algorithm || empty(self::$optiClasses[$algorithm])) return false;
        switch ($algorithm) {
            case 'jpegoptim':
                $obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
                break;
            case 'optipng':
                $obj = \Dev2fun\ImageCompress\Optipng::getInstance();
                break;
        }
        //$obj = self::$optiClasses[$algorithm]::getInstance(); // PHP7+
        $check = $obj->isPNGOptim();
        if (!$check) self::$lastError = $obj->lastError;
        return $check;
    }

    /**
     * @param string $algorithm
     * @return bool
     * @deprecated
     * @uses Check::isOptim()
     */
    public static function isJPEGOptim($algorithm)
    {
        if (!$algorithm || empty(self::$optiClasses[$algorithm])) return false;
        Loader::includeModule(\Dev2funImageCompress::MODULE_ID);
        switch ($algorithm) {
            case 'jpegoptim':
                $obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
                break;
            case 'optipng':
                $obj = \Dev2fun\ImageCompress\Optipng::getInstance();
                break;
        }
        //$obj = self::$optiClasses[$algorithm]::getInstance(); // PHP7+
        $check = $obj->isJPEGOptim();
        if (!$check) self::$lastError = $obj->lastError;
        return $check;
    }

    /**
     * @param string $algorithm
     * @return bool
     */
    public static function isOptim($algorithm)
    {
        if (!$algorithm || empty(self::$optiClasses[$algorithm])) return false;
        Loader::includeModule(\Dev2funImageCompress::MODULE_ID);
        switch ($algorithm) {
            case 'jpegoptim':
                $obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
                break;
            case 'optipng':
                $obj = \Dev2fun\ImageCompress\Optipng::getInstance();
                break;
            case 'ps2pdf':
                $obj = \Dev2fun\ImageCompress\Ps2Pdf::getInstance();
                break;
            case 'webp':
            case 'cwebp':
                $obj = \Dev2fun\ImageCompress\Webp::getInstance();
                break;
            case 'gif':
            case 'gifsicle':
                $obj = \Dev2fun\ImageCompress\Gif::getInstance();
                break;
            case 'svg':
            case 'svgo':
                $obj = \Dev2fun\ImageCompress\Svg::getInstance();
                break;
        }
        //$obj = self::$optiClasses[$algorithm]::getInstance(); // PHP7+
        $check = $obj->isOptim();
        if (!$check) self::$lastError = $obj->lastError;
        return $check;
    }

    /**
     * Return check active mode for mime type
     * @param string $mimeType
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function isActiveByMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg' :
                return Option::get(\Dev2funImageCompress::MODULE_ID, 'enable_jpeg', 'N') === 'Y';
                break;
            case 'image/png' :
                return Option::get(\Dev2funImageCompress::MODULE_ID, 'enable_png', 'N') === 'Y';
                break;
            case 'application/pdf' :
                return Option::get(\Dev2funImageCompress::MODULE_ID, 'enable_pdf', 'N') === 'Y';
                break;
            case 'image/svg' :
                return Option::get(\Dev2funImageCompress::MODULE_ID, 'enable_svg', 'N') === 'Y';
                break;
            case 'image/gif' :
                return Option::get(\Dev2funImageCompress::MODULE_ID, 'enable_gif', 'N') === 'Y';
        }

        return false;
    }

    public static function isRead($path)
    {
        return \is_readable($path);
    }

    public static function isWrite($path)
    {
        return \is_writable($path);
    }

    /**
     * Делает проверку системы на корректность
     * @return bool
     * @deprecated
     */
    public static function system()
    {
        $success = false;
        try {
            $algorithmJpeg = Option::get(\Dev2funImageCompress::MODULE_ID, 'opti_algorithm_jpeg');
            $algorithmPng = Option::get(\Dev2funImageCompress::MODULE_ID, 'opti_algorithm_png');

            if (!$algorithmJpeg)
                throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NOT_CHOICE', ['#ALGORITHM#' => 'JPEG']));
            if ($algorithmJpeg === 'jpegoptim' && !Option::get(\Dev2funImageCompress::MODULE_ID, 'path_to_jpegoptim'))
                throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_PATH', ['#MODULE#' => 'jpegoptim']));
            if (!self::isJPEGOptim($algorithmJpeg)) {
                if (!self::$lastError)
                    self::$lastError = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_IN_ALGORITHM', ['#ALGORITHM#' => 'JPEG']);
                throw new \Exception(self::$lastError);
            }

            if (!$algorithmPng) {
                throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NOT_CHOICE', ['#ALGORITHM#' => 'PNG']));
            }
            if ($algorithmPng === 'optipng' && !Option::get(\Dev2funImageCompress::MODULE_ID, 'path_to_optipng')) {
                throw new \Exception('Не указан путь до optipng');
            }
            if (!self::isPNGOptim($algorithmPng)) {
                if (!self::$lastError)
                    self::$lastError = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_IN_ALGORITHM', ['#ALGORITHM#' => 'PNG']);
                throw new \Exception(self::$lastError);
            }
            $success = true;
        } catch (\Exception $e) {
            self::$lastError = $e->getMessage();
        }
        return $success;
    }

    /**
     * Get algorithm class
     * @param string $algorithm
     * @return null|Jpegoptim|Optipng|Ps2Pdf|Webp|Gif|Svg
     */
    public static function getAlgInstance($algorithm)
    {
        return Compress::getAlgInstance($algorithm);
//        switch ($algorithm) {
//            case 'jpegoptim':
//                $obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
//                break;
//            case 'optipng':
//                $obj = \Dev2fun\ImageCompress\Optipng::getInstance();
//                break;
//            case 'ps2pdf':
//                $obj = \Dev2fun\ImageCompress\Ps2Pdf::getInstance();
//                break;
//            case 'webp':
//                $obj = \Dev2fun\ImageCompress\Webp::getInstance();
//                break;
//            case 'gif':
//                $obj = \Dev2fun\ImageCompress\Gif::getInstance();
//                break;
//            case 'svg':
//                $obj = \Dev2fun\ImageCompress\Svg::getInstance();
//                break;
//        }
//        return $obj;
        //return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
    }
}