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

class Ps2Pdf
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '';
    private $enable = false;

    private function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_ps2pdf');
        $this->enable = Option::get($this->MODULE_ID, 'enable_pdf', false);
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
    public function isPdfOptim()
    {
        exec($this->path . '/gs -v', $s);
        return ($s ? true : false);
    }

    /**
     * Проверка возможности оптимизации pdf
     * @return bool
     */
    public function isOptim()
    {
        return $this->isPdfOptim();
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
            "OnBeforeResizeImagePs2Pdf",
            [&$strFilePath, &$params]
        );
        $event->send();

        $strFilePathNew = $strFilePath.'.pdf';
        $strCommand = '';

        exec($this->path . "/ps2pdf $strCommand $strFilePath $strFilePathNew 2>&1", $res);

        if(file_exists($strFilePathNew)) {
            unlink($strFilePath);
            rename($strFilePathNew, $strFilePath);
        }

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
}