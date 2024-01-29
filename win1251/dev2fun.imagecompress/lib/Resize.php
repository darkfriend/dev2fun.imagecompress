<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 26.08.2018
 * Time: 20:40
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

class Resize
{
    private static $instance;
    public $lastError;

    private $MODULE_ID = 'dev2fun.imagecompress';
    private $width = '';
    private $height = '';
    private $algorithm = '';
    private $enable = false;

    /**
     * @param string|null $siteId
     */
    public function __construct(?string $siteId = null)
    {
//        if (!$siteId) {
//            $siteId = \Dev2funImageCompress::getSiteId();
//        }
        $this->width = Option::get($this->MODULE_ID, 'resize_width', '');
        $this->height = Option::get($this->MODULE_ID, 'resize_height', '');
        $this->algorithm = Option::get($this->MODULE_ID, 'resize_algorithm', '');
        $this->enable = Option::get($this->MODULE_ID, 'resize_enable', 'N') === 'Y';
        if (!$this->algorithm) {
            $this->algorithm = BX_RESIZE_IMAGE_PROPORTIONAL;
        }
    }

    /**
     * @static
     * @return Resize
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function resize($strFilePath)
    {
        if (!$strFilePath || !$this->enable) {
            return false;
        }
        $destinationFile = $_SERVER['DOCUMENT_ROOT'] . "/upload/{$this->MODULE_ID}/" . basename($strFilePath);
        $res = \CFile::ResizeImageFile(
            $strFilePath,
            $destinationFile,
            [
                'width' => $this->width,
                'height' => $this->height,
            ],
            $this->algorithm
        );
        if ($res) {
            chmod($destinationFile, 0777);
            copy($destinationFile, $strFilePath);
            unlink($destinationFile);
        }
        return $res;
    }
}