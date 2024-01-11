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

class Ps2Pdf
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '';
    private $enable = false;
    private $pdfSetting = 'ebook';
    private static $isOptim = null;

    private function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_ps2pdf', '', \Dev2funImageCompress::getSiteId());
        $this->enable = Option::get($this->MODULE_ID, 'enable_pdf', 'N', \Dev2funImageCompress::getSiteId()) === 'Y';
        $this->pdfSetting = Option::get($this->MODULE_ID, 'pdf_setting', 'ebook', \Dev2funImageCompress::getSiteId());
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
     * Check available convert ro pdf
     * @return bool
     */
    public function isPdfOptim()
    {
        if (self::$isOptim === null) {
            \exec($this->path . '/gs -v', $s);
            self::$isOptim = $s ? true : false;
        }
        return self::$isOptim;
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
        $strFilePath = \strtr(
            $strFilePath,
            [
                ' ' => '\ ',
                '(' => '\(',
                ')' => '\)',
                ']' => '\]',
                '[' => '\[',
            ]
        );
        if(!isset($params['pdfSetting'])) {
            $params['pdfSetting'] = $this->pdfSetting;
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeResizeImagePs2Pdf",
            [&$strFilePath, &$params]
        );
        $event->send();

        $strFilePathNew = $strFilePath.'.pdf';
        $strCommand = "-sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/{$params['pdfSetting']} -dNOPAUSE -dQUIET -dBATCH";

        \exec($this->path . "/gs {$strCommand} -sOutputFile='{$strFilePathNew}' '{$strFilePath}' 2>&1", $res);
//        exec($this->path . "/ps2pdf $strCommand $strFilePath $strFilePathNew 2>&1", $res);

        if(\file_exists($strFilePathNew)) {
            \unlink($strFilePath);
            \rename($strFilePathNew, $strFilePath);
        }

        if (!empty($params['changeChmod'])) {
            \chmod($strFilePath, $params['changeChmod']);
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