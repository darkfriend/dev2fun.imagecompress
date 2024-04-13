<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.5
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Svg
{
    private static $instance;
    private static $isOptim = null;
    public $lastError;
    public $binaryName = 'svgo';
    private $MODULE_ID = 'dev2fun.imagecompress';
    /** @var string путь до svgo */
    private $path = '';
    /** @var string путь до nodejs */
    public $pathNodejs;
    private $active = false;

    /**
     * @param string|null $siteId
     */
    public function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_svg', '/usr/bin');
        $this->pathNodejs = Option::get($this->MODULE_ID, 'path_to_node', '/usr/bin');
        $this->active = Option::get($this->MODULE_ID, 'enable_svg', 'N') === 'Y';
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
     * Return has active state
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Check available optimization svg
     * @param string|null $path
     * @param string|null $pathNodejs
     * @return bool
     */
    public function isOptim(?string $path = null, ?string $pathNodejs = null): bool
    {
        if (!$path) {
            $path = $this->path;
        }
        if (!$pathNodejs) {
            $pathNodejs = $this->pathNodejs;
        }
        if (self::$isOptim === null || $path !== $this->path || $pathNodejs !== $this->pathNodejs) {
            exec("{$pathNodejs}/node {$path}/{$this->binaryName} -v", $s);
            self::$isOptim = (bool)$s;
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
        if (!$this->active) {
            $this->lastError = 'no_active';
            return false;
        }
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
            "{$this->pathNodejs}/node {$this->path}/{$this->binaryName} $strCommand --input=$strFilePath --output=$strFilePath 2>&1",
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