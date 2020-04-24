<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.4.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Svg
{
    private static $instance;
    public $lastError;
    public $binaryName = 'svgo';

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '';
    private $enable = false;

    private function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_svg', '/usr/bin');
        $this->enable = Option::get($this->MODULE_ID, 'enable_svg', false);
    }

    /**
     * @static
     * @return Ps2Pdf
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
     * Проверка возможности оптимизации pdf
     * @return bool
     */
    public function isOptim()
    {
        exec($this->path . "/{$this->binaryName} -v", $s);
        return ($s ? true : false);
    }

    /**
     * Процесс оптимизации JPEG
     * @param string $strFilePath - абсолютный путь до картинки
     * @param array $params - дополнительные параметры
     * @return bool
     * @throws \Exception
     */
    public function compress($strFilePath, $params = [])
    {
        if(!$this->enable) return false;
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

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeResizeImageSvg",
            [&$strFilePath, &$params]
        );
        $event->send();

        $strCommand = '';

        exec(
            "{$this->path}/{$this->binaryName} $strCommand --input=$strFilePath --output=$strFilePath 2>&1",
            $res
        );

//        if(file_exists($strFilePathNew)) {
//            unlink($strFilePath);
//            rename($strFilePathNew, $strFilePath);
//        }

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