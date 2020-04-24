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

class Webp
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $path = '';
    private $enable = false;

    private function __construct()
    {
        $this->path = Option::get($this->MODULE_ID, 'path_to_webp', '/usr/bin');
        $this->enable = Option::get($this->MODULE_ID, 'enable_webp', false);
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
        exec($this->path . '/cwebp -version', $s);
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
            "OnBeforeResizeImageWebp",
            [&$strFilePath, &$params]
        );
        $event->send();

//        $strFilePathNew = $strFilePath.'.webp';
        $strCommand = '-lossless ';
        if(!empty($params['compression'])) {
            $strCommand .= "-m {$params['compression']} ";
        }
        if(!empty($params['multithreading'])) {
            $strCommand .= "-mt ";
        }
        if(!empty($params['quality'])) {
            $strCommand .= "-q {$params['quality']} ";
        }

        exec($this->path . "/cwebp $strCommand $strFilePath 2>&1", $res);

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
}