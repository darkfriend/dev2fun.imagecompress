<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.4
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Jpegoptim
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $jpegOptimPath = '';

    private function __construct()
    {
        $this->jpegOptimPath = Option::get($this->MODULE_ID, 'path_to_jpegoptim');
    }

    /**
     * @static
     * @return Compress
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
     * Проверка возможности оптимизации jpeg
     * @return bool
     * @deprecated
     * @uses isOptim()
     */
    public function isJPEGOptim()
    {
        return $this->isOptim();
    }

    /**
     * Проверка возможности оптимизации jpeg
     * @return bool
     */
    public function isOptim()
    {
        exec($this->jpegOptimPath . '/jpegoptim --version', $s);
        return ($s ? true : false);
    }

    /**
     * Процесс оптимизации JPEG
     * @param string $strFilePath - абсолютный путь до картинки
     * @param int $quality - качество
     * @param array $params - дополнительные параметры
     * @return bool
     * @throws \Exception
     * @deprecated
     * @uses compress()
     */
    public function compressJPG($strFilePath, $quality = 80, $params = [])
    {
        return $this->compress($strFilePath, $quality, $params);
    }

    /**
     * Процесс оптимизации JPEG
     * @param string $strFilePath - абсолютный путь до картинки
     * @param int $quality - качество
     * @param array $params - дополнительные параметры
     * @return bool
     * @throws \Exception
     */
    public function compress($strFilePath, $quality = 80, $params = [])
    {
        $strFilePath = strtr(
            $strFilePath,
            [
                ' ' => '\ ',
                '(' => '\(',
                ')' => '\)',
                ']' => '\]',
                '[' => '\[',
            ]
        );
//		foreach (GetModuleEvents($this->MODULE_ID, "OnBeforeResizeImageJpegoptim", true) as $arEvent)
//			ExecuteModuleEventEx($arEvent, array(&$strFilePath, &$quality, &$params));

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeResizeImageJpegoptim",
            [&$strFilePath, &$quality, &$params]
        );
        $event->send();

        $strCommand = '';
        if (!empty($params['progressiveJpeg'])) {
            $strCommand .= '--all-progressive';
        }
        $strCommand .= ' --strip-all -t';
        if ($quality) {
            $strCommand .= " -m{$quality}";
        }
        exec($this->jpegOptimPath . "/jpegoptim $strCommand '$strFilePath' 2>&1", $res);

        if (!empty($params['changeChmod'])) {
            chmod($strFilePath, $params['changeChmod']);
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnAfterResize",
            [&$strFilePath]
        );
        $event->send();
        return true;
    }

    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }
}