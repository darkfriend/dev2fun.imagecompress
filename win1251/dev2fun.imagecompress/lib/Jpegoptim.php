<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.11.10
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Jpegoptim
{
    private static $instance;
    private static $isOptim = null;

    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $jpegOptimPath = '';
    private $active = false;

    public function __construct()
    {
        $this->jpegOptimPath = Option::get($this->MODULE_ID, 'path_to_jpegoptim', '');
        $this->active = Option::get($this->MODULE_ID, 'enable_jpeg', 'N') === 'Y';
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
     * Return has active state
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Check available optimization jpeg
     * @param string|null $path
     * @param bool $exception
     * @return bool
     */
    public function isOptim(?string $path = null, bool $exception = false): bool
    {
        if (!$path) {
            $path = $this->jpegOptimPath;
        }
        if (self::$isOptim === null || $path !== $this->jpegOptimPath) {
            if (!\Dev2funImageCompress::checkAvailable("{$path}/jpegoptim")) {
                self::$isOptim = false;
                if ($exception) {
                    throw new \Exception("{$path}/jpegoptim no readable or executable");
                }
            } else {
                exec("{$path}/jpegoptim --version", $s);
                self::$isOptim = (bool)$s;
            }
        }
        return self::$isOptim;
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
        if (!$this->isActive()) {
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
            "OnBeforeResizeImageJpegoptim",
            [&$strFilePath, &$quality, &$params]
        );
        $event->send();

        $strCommand = '';
        if (!empty($params['progressiveJpeg'])) {
            $strCommand .= '--all-progressive';
        }
//        $strCommand .= ' --strip-all -t';
//        $strCommand .= ' --strip-com --strip-iptc --strip-xmp --strip-jfif --strip-jfxx --strip-Adobe --totals --preserve --preserve-perms';
        $strCommand .= ' --strip-com --strip-iptc --strip-xmp --totals --preserve --preserve-perms';
        if ($quality) {
            $strCommand .= " -m{$quality}";
        }

        $exif = exif_read_data($strFilePath);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3: // Rotate 180 degrees
                case 6: // Rotate 90 degrees CW
                case 8: // Rotate 90 degrees CCW
                    $strCommand .= ' --keep-exif';
                    break;
                default:
                    $strCommand .= ' --strip-exif';
            }
        }

        exec($this->jpegOptimPath . "/jpegoptim $strCommand '$strFilePath' 2>&1", $res);

//        if (!empty($params['changeChmod'])) {
//            chmod($strFilePath, $params['changeChmod']);
//        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnAfterResize",
            [&$strFilePath]
        );
        $event->send();
        return true;
    }

    /**
     * @param array $advanceSettings
     * @return array
     */
    public function getOptionsSettings($advanceSettings=[])
    {
        return [];
    }
}