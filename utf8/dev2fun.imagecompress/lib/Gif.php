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

class Gif
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '';
    private $active = false;
    private static $isOptim = null;

    public function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_gif', '/usr/bin');
        $this->active = Option::get($this->MODULE_ID, 'enable_gif', 'N') === 'Y';
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
     * Check optimize for gif
     * @param string|null $path
     * @return bool
     */
    public function isOptim(?string $path = null)
    {
        if (!$path) {
            $path = $this->path;
        }
        if (self::$isOptim === null || $path !== $this->path) {
            exec($path . '/gifsicle --version', $s);
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
        if(!$this->isActive()) {
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

        if(empty($params['compression'])) {
            $params['compression'] = Option::get($this->MODULE_ID, 'gif_compress', 2);
        }

        $event = new \Bitrix\Main\Event(
            $this->MODULE_ID,
            "OnBeforeResizeImageGif",
            [&$strFilePath, &$params]
        );
        $event->send();

        $strCommand = '';
        if(!empty($params['compression'])) {
            $strCommand .= "-O{$params['compression']} ";
        }

        exec($this->path . "/gifsicle $strCommand $strFilePath -o $strFilePath 2>&1", $res);

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
        $settings = [
            'gif_compress' => 'string',
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
}