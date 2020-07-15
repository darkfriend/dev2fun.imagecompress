<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.5.2
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
        $this->quality = Option::get($this->MODULE_ID, 'webp_quality', 80);
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
     * Проверка возможности конвертирования
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
     * Процесс оптимизации JPEG
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
        $src = "{$_SERVER["DOCUMENT_ROOT"]}/$uploadDir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        $fileInfo = \pathinfo($src);
        $srcWebp = "/{$uploadDir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$fileInfo['filename']}.webp";
        $absSrcWebp = $_SERVER["DOCUMENT_ROOT"].$srcWebp;

//        $upload_dir = Option::get('main', 'upload_dir', 'upload');
//        $src = "{$_SERVER["DOCUMENT_ROOT"]}/$upload_dir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
//        $srcWebp = "/{$upload_dir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$arFile['FILE_NAME']}.webp";

        if(\is_file($absSrcWebp)) {
            return $srcWebp;
        }
        $dirname = \dirname($absSrcWebp);

        if(!\is_dir($dirname)) {
            \mkdir($dirname,0777, true);
        }
//        var_dump($absSrcWebp); die();
//        switch($arFile['CONTENT_TYPE']) {
        switch(\mime_content_type($src)) {
            case 'image/png':
                $img = \imageCreateFromPng($src);
                break;
            case 'image/jpeg':
                $img = \imageCreateFromJpeg($src);
                break;
        }
        if(empty($img)) {
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