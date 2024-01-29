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
    private $pdfSetting = 'ebook';
    private static $isOptim = null;
    private $active = false;

    /**
     * @param string|null $siteId
     */
    public function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_ps2pdf', '');
        $this->active = Option::get($this->MODULE_ID, 'enable_pdf', 'N') === 'Y';
        $this->pdfSetting = Option::get($this->MODULE_ID, 'pdf_setting', 'ebook');
    }

    /**
     * Return has active state
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
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
     * Check available optimization pdf
     * @return bool
     * @deprecated
     * @uses isOptim()
     */
    public function isPdfOptim()
    {
        return $this->isOptim();
    }

    /**
     * Check available optimization pdf
     * @param string|null $path
     * @return bool
     */
    public function isOptim(?string $path = null)
    {
        if (!$path) {
            $path = $this->path;
        }
        if (self::$isOptim === null || $path !== $this->path) {
            \exec($path . '/gs -v', $s);
            self::$isOptim = (bool)$s;;
        }
        return self::$isOptim;
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
        if(!$this->active) {
            $this->lastError = 'no_active';
            return false;
        }
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