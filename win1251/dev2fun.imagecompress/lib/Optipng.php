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

class Optipng
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $pngOptimPath = '';

    private function __construct()
    {
        $this->pngOptimPath = Option::get($this->MODULE_ID, 'path_to_optipng');
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
     * Проверка возможности оптимизации PNG
     * @return bool
     * @deprecated
     * @uses isOptim()
     */
    public function isPNGOptim()
    {
        return $this->isOptim();
    }

    /**
     * Проверка возможности оптимизации PNG
     * @return bool
     */
    public function isOptim()
    {
        exec($this->pngOptimPath . '/optipng -v', $s);
        return ($s ? true : false);
    }


    /**
     * Процесс оптимизации PNG
     * @param string $strFilePath - абсолютный путь до картинки
     * @param int $quality - качество от 1 до 7
     * @param array $params - дополнительные параметры
     * @return bool
     * @throws \Exception
     * @deprecated
     * @uses compress()
     */
    public function compressPNG($strFilePath, $quality = 3, $params = [])
    {
        return $this->compress($strFilePath, $quality, $params);
    }

    /**
     * Процесс оптимизации PNG
     * @param string $strFilePath - абсолютный путь до картинки
     * @param int $quality - качество от 1 до 7
     * @param array $params - дополнительные параметры
     * @return bool
     * @throws \Exception
     */
    public function compress($strFilePath, $quality = 3, $params = [])
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
//		foreach (GetModuleEvents($this->MODULE_ID, "OnBeforeResizeImageOptipng", true) as $arEvent)
//			ExecuteModuleEventEx($arEvent, array(&$strFilePath, &$quality));

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeResizeImageOptipng",
            [&$strFilePath, &$quality, &$params]
        );
        $event->send();

        exec($this->pngOptimPath . "/optipng -v", $out);
        $execString = "-strip all -o{$quality} '$strFilePath' 2>&1";
        if (!empty($out[0])) {
            if (preg_match('#optipng.(.*?)\:#i', $out[0], $vMatch)) {
                $vMatch = preg_replace('#(\.)#', '', $vMatch[1]);
                if ($vMatch && $vMatch < 70) {
                    $execString = "-o{$quality} '$strFilePath' 2>&1";
                }
            }
        }
        exec($this->pngOptimPath . "/optipng {$execString}", $res);

        if (!empty($params['changeChmod'])) {
            chmod($strFilePath, $params['changeChmod']);
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnAfterResizeImage",
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