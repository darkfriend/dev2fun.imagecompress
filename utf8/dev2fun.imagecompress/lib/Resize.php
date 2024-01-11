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

	private function __construct()
    {
		$this->width = Option::get($this->MODULE_ID,'resize_width', '', \Dev2funImageCompress::getSiteId());
		$this->height = Option::get($this->MODULE_ID,'resize_height', '', \Dev2funImageCompress::getSiteId());
		$this->algorithm = Option::get($this->MODULE_ID,'resize_algorithm', '', \Dev2funImageCompress::getSiteId());
		$this->enable = Option::get($this->MODULE_ID,'resize_enable', 'N', \Dev2funImageCompress::getSiteId())==='Y';
		if (!$this->algorithm) {
            $this->algorithm = BX_RESIZE_IMAGE_PROPORTIONAL;
        }
	}

	/**
	 * @static
	 * @return Resize
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	public function resize($strFilePath) {
		if(!$strFilePath || !$this->enable) return false;
		$destinationFile = $_SERVER['DOCUMENT_ROOT']."/upload/{$this->MODULE_ID}/".basename($strFilePath);
		$res = \CFile::ResizeImageFile(
			$strFilePath,
			$destinationFile,
			array(
				'width'=>$this->width,
				'height'=>$this->height
			),
			$this->algorithm
		);
		if($res) {
			chmod($destinationFile,0777);
			copy($destinationFile,$strFilePath);
			unlink($destinationFile);
		}
		return $res;
	}
}