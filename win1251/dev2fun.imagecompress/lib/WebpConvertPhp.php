<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.4
 */

namespace Dev2fun\ImageCompress;


use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class WebpConvertPhp
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $enable = false;
    private $quality = 70;
    private static $isOptim = null;
    private $origPicturesMode = false;
    private static $origPictures = [];

    /**
     * @param string|null $siteId
     */
    public function __construct(?string $siteId = null)
    {
        if (!$siteId) {
            $siteId = \Dev2funImageCompress::getSiteId();
        }
        $this->enable = Option::get($this->MODULE_ID, 'convert_enable', 'N', $siteId) === 'Y';
        $this->origPicturesMode = Option::get($this->MODULE_ID, 'orig_pictures_mode', 'N', $siteId) === 'Y';
        $this->quality = Option::get($this->MODULE_ID, 'convert_quality', 80, $siteId);
        if (!$this->quality) {
            $this->quality = 80;
        }
    }

    /**
     * @static
     * @return WebpConvertPhp
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /**
     * Check imagewebp and gd
     * @return bool
     */
    public function isOptim()
    {
        if(!\function_exists('imagewebp')) {
            throw new \ErrorException('Not found "imagewebp"');
        }
        if(!\extension_loaded('gd')) {
            throw new \ErrorException('Not found "gd"');
        }
        return true;
    }

    /**
     * Process convert
     * @param array $arFile
     * @param array $params - дополнительные параметры
     * @return bool|null
     * @throws \Exception
     */
    public function convert($arFile, $params = [])
    {
        if(!$this->enable) return false;
//        \darkfriend\helpers\DebugHelper::print_pre($arFile, 1);
        //        $strFilePath = strtr(
        //            $strFilePath,
        //            [
        //                ' ' => '\ ',
        //                '(' => '\(',
        //                ')' => '\)',
        //                ']' => '\]',
        //                '[' => '\[',
        //            ]
        //        );

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeConvertImageWebp",
            [&$arFile, &$params]
        );
        $event->send();

        $uploadDir = Option::get('main', 'upload_dir', 'upload');
        if(!empty($arFile["ABS_PATH"])) {
            $src = $arFile["ABS_PATH"];
        } else {
            $src = "{$_SERVER["DOCUMENT_ROOT"]}/$uploadDir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        }

        $fileInfo = \pathinfo($src);
        $arFile["SUBDIR"] = \str_replace("/{$uploadDir}",'', $arFile["SUBDIR"]);
        $arFile["SUBDIR"] = \ltrim($arFile["SUBDIR"], '/');
//        $srcWebp = "/{$uploadDir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.webp";
        $origPicture = "/{$uploadDir}";
        $srcWebp = "/{$uploadDir}/resize_cache/webp";
        if (!empty($arFile["SUBDIR"])) {
            $srcWebp .= "/{$arFile["SUBDIR"]}";
            $origPicture .= "/{$arFile["SUBDIR"]}";
        }
        $origPicture .= "/{$arFile['FILE_NAME']}";
        $srcWebp .= "/{$fileInfo['filename']}.webp";
        $absSrcWebp = $_SERVER["DOCUMENT_ROOT"].$srcWebp;

        if(@\is_file($absSrcWebp)) {
            if(\filesize($absSrcWebp)===0) {
                return false;
            }
            if ($this->origPicturesMode) {
                self::$origPictures[$srcWebp] = $origPicture;

            }
            return $srcWebp;
        }
        $dirname = \dirname($absSrcWebp);

        if(!\is_dir($dirname)) {
            if(!@\mkdir($dirname,0777, true)) {
                return false;
            }
        }

        switch (\mime_content_type($src)) {
            case 'image/png':
                $img = \imageCreateFromPng($src);
                if ($img && (is_resource($img) || $img instanceof \GdImage)) {
                    \imagepalettetotruecolor($img);
                }
                break;
            case 'image/jpeg':
                $img = \imageCreateFromJpeg($src);
                break;
        }
        if (empty($img)) {
            return false;
        }
        \imageWebp(
            $img,
            $absSrcWebp,
            $this->quality
        );
        \imagedestroy($img);

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnAfterConvert",
            [&$srcWebp]
        );
        $event->send();

        if ($this->origPicturesMode) {
            self::$origPictures[$srcWebp] = $origPicture;
        }

        return $srcWebp;
    }

    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }

    public function convertPath($srcWebp)
    {
        $uploadDir = Option::get('main', 'upload_dir', 'upload');
        if(!empty($arFile["ABS_PATH"])) {
            $src = $arFile["ABS_PATH"];
        } else {
            $src = "{$_SERVER["DOCUMENT_ROOT"]}/$uploadDir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        }

        $fileInfo = \pathinfo($src);
//        $srcWebp = "/{$uploadDir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.webp";
        $absSrcWebp = $_SERVER["DOCUMENT_ROOT"].$srcWebp;

        if(@\is_file($absSrcWebp)) {
            if(\filesize($absSrcWebp)===0) {
                return false;
            }
            if ($this->origPicturesMode) {
                self::$origPictures[$srcWebp] = "/{$uploadDir}/{$arFile["SUBDIR"]}/{$arFile['FILE_NAME']}";
            }
            return $srcWebp;
        }
        $dirname = \dirname($absSrcWebp);

        if(!\is_dir($dirname)) {
            if(!@\mkdir($dirname,0777, true)) {
                return false;
            }
        }

        switch (\mime_content_type($src)) {
            case 'image/png':
                $img = \imageCreateFromPng($src);
                if ($img && (is_resource($img) || $img instanceof \GdImage)) {
                    \imagepalettetotruecolor($img);
                }
                break;
            case 'image/jpeg':
                $img = \imageCreateFromJpeg($src);
                break;
        }
        if (empty($img)) {
            return false;
        }
        \imageWebp(
            $img,
            $absSrcWebp,
            $this->quality
        );
        \imagedestroy($img);
    }

    /**
     * Get original src by webp src
     * @param string $srcWebp
     * @return string
     */
    public function getOriginalSrc(string $srcWebp): string
    {
        if (!$this->origPicturesMode) {
            return '';
        }
        return self::$origPictures[$srcWebp] ?? '';
    }
}