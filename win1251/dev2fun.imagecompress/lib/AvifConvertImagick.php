<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.0
 */

namespace Dev2fun\ImageCompress;


use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class AvifConvertImagick
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $enable = false;
    private $quality = 80;

    private function __construct()
    {
        $this->enable = Option::get($this->MODULE_ID, 'convert_enable', 'N') === 'Y';
        $this->quality = Option::get($this->MODULE_ID, 'convert_quality', 80);
        if(!$this->quality) {
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
    public function isOptim()
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
        $arFile["SUBDIR"] = \str_replace("/{$uploadDir}/resize_cache",'', $arFile["SUBDIR"]);
        $arFile["SUBDIR"] = \ltrim($arFile["SUBDIR"], '/');
        $srcWebp = "/{$uploadDir}/resize_cache/avif/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.avif";
        $absSrcWebp = $_SERVER["DOCUMENT_ROOT"].$srcWebp;

        if(@\is_file($absSrcWebp)) {
            if(\filesize($absSrcWebp)===0) {
                return false;
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
        $imagick->destroy();

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

        return $srcWebp;
    }

    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }
}