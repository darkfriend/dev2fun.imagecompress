<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.5
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
        $arFile["SUBDIR"] = \str_replace("/{$uploadDir}/resize_cache",'', $arFile["SUBDIR"]);
        $arFile["SUBDIR"] = \ltrim($arFile["SUBDIR"], '/');
        $srcWebp = "/{$uploadDir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.webp";
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

        return $srcWebp;
    }

    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }
}