<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.4
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Webp
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '/usr/bin';
    private $enable = false;
    private $quality = 80;
    private $compression = 4;
    private $multithreading = true;
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

        $this->path = Option::get($this->MODULE_ID, 'path_to_cwebp', '/usr/bin', $siteId);
        $this->enable = Option::get($this->MODULE_ID, 'convert_enable', 'N', $siteId) === 'Y';
        $this->origPicturesMode = Option::get($this->MODULE_ID, 'orig_pictures_mode', 'N', $siteId) === 'Y';

        $this->quality = Option::get($this->MODULE_ID, 'convert_quality', 80, $siteId);
        if(!$this->quality) {
            $this->quality = 80;
        }

        $this->compression = Option::get($this->MODULE_ID, 'cwebp_compress', 4, $siteId);
        if(!$this->compression && $this->compression!==0) {
            $this->compression = 4;
        }

        $this->multithreading = Option::get($this->MODULE_ID, 'cwebp_multithreading', 'Y', $siteId) === 'Y';
    }

    /**
     * @static
     * @return Webp
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
     * Check available cwebp
     * @param string|null $path
     * @return bool
     */
    public function isOptim(?string $path = null)
    {
        if (!$path) {
            $path = $this->path;
        }
        if (self::$isOptim === null || $path !== $this->path) {
            exec($path . '/cwebp -version', $s);
            self::$isOptim = (bool)$s;
        }
        return self::$isOptim;
    }

    /**
     * Process convert
     * @param array $arFile
     * @param array $params - дополнительные параметры
     * @return bool
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
        $absSrcWebp = $_SERVER['DOCUMENT_ROOT'].$srcWebp;

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

        if(!isset($params['compression'])) {
            $params['compression'] = $this->compression;
        }
        if(!isset($params['multithreading'])) {
            $params['multithreading'] = $this->multithreading;
        }
        if(!isset($params['quality'])) {
            $params['quality'] = $this->quality;
        }
        if(!isset($params['changeChmod'])) {
            $params['changeChmod'] = 0777;
        }

        $strCommand = '';
        if(!empty($params['compression']) || $params['compression']===0) {
            $strCommand .= "-m {$params['compression']} ";
        }
        if(!empty($params['multithreading'])) {
            $strCommand .= "-mt ";
        }
        if(!empty($params['quality']) && (int)$params['quality'] !== 100) {
            $strCommand .= "-q {$params['quality']} ";
        } else {
            $strCommand .= '-lossless ';
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeCWebpConvert",
            [&$src, &$absSrcWebp, &$strCommand]
        );
        $event->send();

        \exec("{$this->path}/cwebp $strCommand '{$src}' -o '{$absSrcWebp}' 2>&1", $res);
        if (!empty($params['changeChmod'])) {
            @\chmod($absSrcWebp, $params['changeChmod']);
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnAfterResize",
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
        $settings = [
            'webp_multithreading' => 'checkbox',
            'webp_compress' => 'string',
            'webp_quality' => 'string',
        ];
        $post = [
            'checkbox' => [],
            'string' => [],
        ];
        foreach ($settings as $option=>$setting) {
            if(empty($advanceSettings[$setting][$option])) {
                if($setting==='checkbox') {
                    $post[$setting][$option] = 'N';
                } else {
                    $post[$setting][$option] = '';
                }
            } else {
                $post[$setting][$option] = $advanceSettings[$setting][$option];
            }
        }
        return $post;
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