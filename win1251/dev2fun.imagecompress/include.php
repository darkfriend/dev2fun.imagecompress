<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.2.1
 */

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

//CModule::IncludeModule("dev2fun.versioncontrol");
global $DBType;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

Loader::registerAutoLoadClasses(
	"dev2fun.imagecompress",
	array(
	    'Dev2fun\ImageCompress\ImageCompressTable' => 'classes/general/ImageCompressTable.php',
	    'Dev2fun\ImageCompress\AdminList' => 'lib/AdminList.php',
	    'Dev2fun\ImageCompress\Check' => 'lib/Check.php',
	    'Dev2fun\ImageCompress\Compress' => 'lib/Compress.php',
		"Dev2funImageCompress" => __FILE__,

		"Dev2fun\ImageCompress\Jpegoptim" => 'lib/Jpegoptim.php',
		"Dev2fun\ImageCompress\Optipng" => 'lib/Optipng.php',
	)
);

class Dev2funImageCompress {

	const MODULE_ID = 'dev2fun.imagecompress';

    public function DoBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu) {
        $aModuleMenu[] = array(
            "parent_menu" => "global_menu_settings",
            "icon" => "dev2fun_compressimage_menu_icon",
            "page_icon" => "dev2fun_compressimage_page_icon",
            "sort"=>"900",
            "text"=> Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TEXT"),
            "title"=> Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TITLE"),
            "url"=>"/bitrix/admin/dev2fun_imagecompress_files.php",
            "items_id" => "menu_dev2fun_compressimage",
            "section" => "dev2fun_imagecompress",
            "more_url"=>array(),
//            "items" => array(
//                array(
//                    "text" => GetMessage("DEV2FUN_IMAGECOMPRESS_SUB_SETINGS_MENU_TEXT"),
//                    "title"=> GetMessage("DEV2FUN_IMAGECOMPRESS_SUB_SETINGS_MENU_TITLE"),
//                    "url"=>"/bitrix/admin/dev2fun_imagecompress_files.php",
//                    "sort"=>"100",
//                    "icon" => "sys_menu_icon",
//                    "page_icon" => "default_page_icon",
//                ),
//            )
        );
    }

	public static function ShowThanksNotice() {
		\CAdminNotify::Add([
			'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage(
				'D2F_IMAGECOMPRESS_DONATE_MESSAGE',
				['#URL#'=>'/bitrix/admin/settings.php?lang=ru&mid=dev2fun.imagecompress&mid_menu=1&tabControl_active_tab=donate']
			),
			'TAG' => 'dev2fun_imagecompress_update',
			'MODULE_ID' => 'dev2fun.imagecompress',
		]);
	}
}