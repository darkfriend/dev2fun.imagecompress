<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.6.6
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
    [
        'Dev2fun\ImageCompress\ImageCompressTable' => 'classes/general/ImageCompressTable.php',
        'Dev2fun\ImageCompress\AdminList' => 'lib/AdminList.php',
        'Dev2fun\ImageCompress\Check' => 'lib/Check.php',
        'Dev2fun\ImageCompress\Compress' => 'lib/Compress.php',
        'Dev2fun\ImageCompress\Convert' => 'lib/Convert.php',
        'Dev2fun\ImageCompress\Process' => 'lib/Process.php',
        "Dev2funImageCompress" => __FILE__,

        "Dev2fun\ImageCompress\Jpegoptim" => 'lib/Jpegoptim.php',
        "Dev2fun\ImageCompress\Optipng" => 'lib/Optipng.php',
        "Dev2fun\ImageCompress\Ps2Pdf" => 'lib/Ps2Pdf.php',
        "Dev2fun\ImageCompress\Webp" => 'lib/Webp.php',
        "Dev2fun\ImageCompress\Gif" => 'lib/Gif.php',
        "Dev2fun\ImageCompress\Svg" => 'lib/Svg.php',
        "Dev2fun\ImageCompress\WebpConvertPhp" => 'lib/WebpConvertPhp.php',
    ]
);

class Dev2funImageCompress
{
    const MODULE_ID = 'dev2fun.imagecompress';

    public static $supportFormats = [
        'jpeg',
        'png',
        'pdf',
        'webp',
        'gif',
        'svg',
    ];

    /**
     * Get protocol
     * @return string
     */
    public static function getProtocol()
    {
        $protocol = 'http';
        if(\CMain::IsHTTPS()) {
            $protocol .= 's';
        }
        return ($protocol.'://');
    }

    /**
     * Get domain
     * @return mixed
     */
    public static function getHost()
    {
        $host = \SITE_SERVER_NAME;
        if(!$host) {
            $host = $_SERVER['HTTP_HOST'];
        }
        return $host;
    }

    /**
     * Get url
     * @param string $path
     * @return bool|string
     */
    public static function getUrl($path='')
    {
        if(!$path) return false;
        return self::getProtocol().self::getHost().$path;
    }

    public static function DoBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        $aModuleMenu[] = [
            "parent_menu" => "global_menu_settings",
            "icon" => "dev2fun_compressimage_menu_icon",
            "page_icon" => "dev2fun_compressimage_page_icon",
            "sort" => "900",
            "text" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TEXT"),
            "title" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TITLE"),
            "url" => "/bitrix/admin/dev2fun_imagecompress_files.php",
            "items_id" => "menu_dev2fun_compressimage",
            "section" => "dev2fun_imagecompress",
            "more_url" => [],
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
        ];
    }

    public static function ShowThanksNotice()
    {
        \CAdminNotify::Add([
            'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage(
                'D2F_IMAGECOMPRESS_DONATE_MESSAGE',
                ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.imagecompress&mid_menu=1&tabControl_active_tab=donate']
            ),
            'TAG' => 'dev2fun_imagecompress_update',
            'MODULE_ID' => 'dev2fun.imagecompress',
        ]);
    }
}