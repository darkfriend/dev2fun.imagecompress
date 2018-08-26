<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.2.2
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class Check
{
	private static $optiClasses = [
		'jpegoptim' => '\Dev2fun\ImageCompress\Jpegoptim',
		'optipng' => '\Dev2fun\ImageCompress\Optipng',
	];

	public static $lastError;

	/**
	 * @param string $algorithm
	 * @return bool
	 */
	public static function isPNGOptim($algorithm) {
		if(!$algorithm || empty(self::$optiClasses[$algorithm])) return false;
		switch ($algorithm) {
			case 'jpegoptim' :
				$obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
				break;
			case 'optipng' :
				$obj = \Dev2fun\ImageCompress\Optipng::getInstance();
				break;
		}
//		$obj = self::$optiClasses[$algorithm]::getInstance(); // PHP7+
		$check = $obj->isPNGOptim();
		if(!$check) self::$lastError = $obj->lastError;
		return $check;

//        $path = Option::get('dev2fun.imagecompress','path_to_optipng', '/usr/bin');
//        exec($path.'/optipng -v',$s);
//        return ($s?true:false);
	}

	/**
	 * @param string $algorithm
	 * @return bool
	 */
	public static function isJPEGOptim($algorithm) {
		if(!$algorithm || empty(self::$optiClasses[$algorithm])) return false;
		Loader::includeModule(\Dev2funImageCompress::MODULE_ID);
		switch ($algorithm) {
			case 'jpegoptim' :
				$obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
				break;
			case 'optipng' :
				$obj = \Dev2fun\ImageCompress\Optipng::getInstance();
				break;
		}
//		$obj = self::$optiClasses[$algorithm]::getInstance(); // PHP7+
		$check =  $obj->isJPEGOptim();
		if(!$check) self::$lastError = $obj->lastError;
		return $check;

//        $path = Option::get('dev2fun.imagecompress','path_to_jpegoptim', '/usr/bin');
//        exec($path.'/jpegoptim --version',$s);
//        return ($s?true:false);
	}

	public static function isRead($path) {
		return is_readable($path);
	}

	public static function isWrite($path) {
		return is_writable($path);
	}

	/**
	 * Делает проверку системы на корректность
	 * @return bool
	 */
	public static function system() {
		$success = false;
		try {
			$algorithmJpeg = Option::get(\Dev2funImageCompress::MODULE_ID,'opti_algorithm_jpeg');
			$algorithmPng = Option::get(\Dev2funImageCompress::MODULE_ID,'opti_algorithm_png');

			if(!$algorithmJpeg)
				throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NOT_CHOICE',['#ALGORITHM#'=>'JPEG']));
			if($algorithmJpeg=='jpegoptim' && !Option::get(\Dev2funImageCompress::MODULE_ID,'path_to_jpegoptim'))
				throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_PATH',['#MODULE#'=>'jpegoptim']));
			if(!self::isJPEGOptim($algorithmJpeg)) {
				if(!self::$lastError)
					self::$lastError = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_IN_ALGORITHM',['#ALGORITHM#'=>'JPEG']);
				throw new \Exception(self::$lastError);
			}

			if(!$algorithmPng)
				throw new \Exception(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NOT_CHOICE',['#ALGORITHM#'=>'PNG']));
			if($algorithmPng=='optipng' && !Option::get(\Dev2funImageCompress::MODULE_ID,'path_to_optipng'))
				throw new \Exception('Не указан путь до optipng');
			if(!self::isPNGOptim($algorithmPng)) {
				if(!self::$lastError)
					self::$lastError = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_IN_ALGORITHM',['#ALGORITHM#'=>'PNG']);
				throw new \Exception(self::$lastError);
			}
			$success = true;
		} catch (\Exception $e) {
			self::$lastError = $e->getMessage();
		}
		return $success;
	}

	public static function getAlgInstance($algorithm) {
		switch ($algorithm) {
			case 'jpegoptim' :
				$obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
				break;
			case 'optipng' :
				$obj = \Dev2fun\ImageCompress\Optipng::getInstance();
				break;
		}
		return $obj;
//		return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
	}
}