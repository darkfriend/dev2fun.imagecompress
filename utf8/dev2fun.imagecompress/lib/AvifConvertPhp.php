<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.4
 */

namespace Dev2fun\ImageCompress;


use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class AvifConvertPhp
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $enable = false;
    private $quality = 80;
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
     * Check imageavif and gd
     * @return bool
     */
    public function isOptim()
    {
        if(!\function_exists('imageavif')) {
            throw new \ErrorException('Not found "imageavif"');
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

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeConvertImageAvif",
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
//        $arFile["SUBDIR"] = \str_replace("/{$uploadDir}/resize_cache",'', $arFile["SUBDIR"]);
        $arFile["SUBDIR"] = \ltrim($arFile["SUBDIR"], '/');
//        $srcWebp = "/{$uploadDir}/resize_cache/avif/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.avif";
        $origPicture = "/{$uploadDir}";
        $srcWebp = "/{$uploadDir}/resize_cache/avif";
        if (!empty($arFile["SUBDIR"])) {
            $srcWebp .= "/{$arFile["SUBDIR"]}";
            $origPicture .= "/{$arFile["SUBDIR"]}";
        }
        $origPicture .= "/{$arFile['FILE_NAME']}";
        $srcWebp .= "/{$fileInfo['filename']}.avif";
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

        switch(\mime_content_type($src)) {
            case 'image/png':
                $img = \imageCreateFromPng($src);
                \imagepalettetotruecolor($img);
                break;
            case 'image/jpeg':
                $img = \imageCreateFromJpeg($src);
                break;
        }
        if(empty($img)) {
            return false;
        }
        imageavif(
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

    /**
     * Get original by src
     * @param string $src
     * @return string
     */
    public function getOriginalSrc(string $src): string
    {
        if (!$this->origPicturesMode) {
            return '';
        }
        return self::$origPictures[$src] ?? '';
    }

    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }
}