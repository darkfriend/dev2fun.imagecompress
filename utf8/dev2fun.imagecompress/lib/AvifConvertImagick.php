<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.8
 */

namespace Dev2fun\ImageCompress;


use Bitrix\Main\Config\Option;
use Dev2funImageCompress;

IncludeModuleLangFile(__FILE__);

class AvifConvertImagick
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $enable = false;
    private $quality = 80;
    private $origPicturesMode = false;
    private static $origPictures = [];

    public function __construct(?string $siteId = null)
    {
        if (!$siteId) {
            $siteId = Dev2funImageCompress::getSiteId();
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
     * Check
     * @return bool
     */
    public function isOptim(): bool
    {
        if(!\class_exists('Imagick')) {
            throw new \ErrorException('Not found "Imagick"');
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
    public function convert($arFile, array $params = [])
    {
        if (!$this->enable) {
            return false;
        }

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
//        $srcWebp = "/{$uploadDir}/resize_cache/avif";
        $srcWebp = Convert::getConvertedPath('', 'avif');
        if (!empty($arFile["SUBDIR"])) {
            $srcWebp .= "/{$arFile["SUBDIR"]}";
            $origPicture .= "/{$arFile["SUBDIR"]}";
        }
        $origPicture .= "/{$arFile['FILE_NAME']}";
//        $srcWebp .= "/{$fileInfo['filename']}.avif";

        $newFileName = $fileInfo['filename'];
        if (!preg_match('#^[\w\-. ]+$#', $newFileName)) {
            $newFileName = md5($newFileName);
        }
        $srcWebp .= "/{$newFileName}.avif";

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

        $imagick = new \Imagick();
        $imagick->readImage($src);
        $imagick->setImageFormat('avif');
        $imagick->setCompressionQuality($this->quality);
        $imagick->writeImage($absSrcWebp);

        if (method_exists($imagick, 'destroy')) {
            $imagick->destroy();
        } else {
            $imagick->clear();
        }

//        switch(\mime_content_type($src)) {
//            case 'image/png':
//                $img = \imageCreateFromPng($src);
//                \imagepalettetotruecolor($img);
//                break;
//            case 'image/jpeg':
//                $img = \imageCreateFromJpeg($src);
//                break;
//        }
//        if(empty($img)) {
//            return false;
//        }
//        imageavif(
//            $img,
//            $absSrcWebp,
//            $this->quality
//        );
//        \imagedestroy($img);

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