<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.9.0
 */

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

global $DBType;

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "dev2fun.imagecompress",
    [
        'Dev2fun\ImageCompress\ImageCompressTable' => 'classes/general/ImageCompressTable.php',

        'Dev2fun\ImageCompress\ImageCompressImagesTable' => 'classes/general/ImageCompressImagesTable.php',
        'Dev2fun\ImageCompress\ImageCompressImagesConvertedTable' => 'classes/general/ImageCompressImagesConvertedTable.php',
        'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable' => 'classes/general/ImageCompressImagesToConvertedTable.php',

        'Dev2fun\ImageCompress\MySqlHelper' => 'classes/general/MySqlHelper.php',

        'Dev2fun\ImageCompress\AdminList' => 'lib/AdminList.php',
        'Dev2fun\ImageCompress\Check' => 'lib/Check.php',
        'Dev2fun\ImageCompress\Compress' => 'lib/Compress.php',
        'Dev2fun\ImageCompress\Convert' => 'lib/Convert.php',
        "Dev2fun\ImageCompress\LazyConvert" => 'lib/LazyConvert.php',
        'Dev2fun\ImageCompress\Process' => 'lib/Process.php',
        'Dev2fun\ImageCompress\Cache' => 'lib/Cache.php',
        "Dev2funImageCompress" => __FILE__,

        "Dev2fun\ImageCompress\Jpegoptim" => 'lib/Jpegoptim.php',
        "Dev2fun\ImageCompress\Optipng" => 'lib/Optipng.php',
        "Dev2fun\ImageCompress\Ps2Pdf" => 'lib/Ps2Pdf.php',
        "Dev2fun\ImageCompress\Webp" => 'lib/Webp.php',
        "Dev2fun\ImageCompress\Gif" => 'lib/Gif.php',
        "Dev2fun\ImageCompress\Svg" => 'lib/Svg.php',
        "Dev2fun\ImageCompress\WebpConvertPhp" => 'lib/WebpConvertPhp.php',
        "Dev2fun\ImageCompress\AvifConvertPhp" => 'lib/AvifConvertPhp.php',
        "Dev2fun\ImageCompress\AvifConvertImagick" => 'lib/AvifConvertImagick.php',
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
        'avif',
        'gif',
        'svg',
    ];

    /** @var string */
    private static $siteId = null;

    /**
     * Get protocol
     * @return string
     */
    public static function getProtocol(): string
    {
        $protocol = 'http';
        if(\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps()) {
            $protocol .= 's';
        }
        return ($protocol.'://');
    }

    /**
     * Get domain
     * @return string
     */
    public static function getHost(): string
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
     * @return string
     */
    public static function getUrl($path=''): string
    {
        if(!$path) return '';
        return self::getProtocol().self::getHost().$path;
    }

    /**
     * Get sites
     * @return array
     */
    public static function getSites(): array
    {
        $sites = [];
        $rsSites = CSite::GetList();
        while ($site = $rsSites->Fetch()) {
            $sites[] = $site;
        }

        return $sites;
    }

    /**
     * Get current site id
     * @return string|null
     */
    public static function getSiteId(): ?string
    {
        if (self::$siteId === null) {
            $siteId = \Bitrix\Main\Application::getInstance()->getContext()->getSite();
            if (!$siteId && !empty($_REQUEST['SITE_ID'])) {
                $siteId = htmlspecialcharsbx($_REQUEST['SITE_ID']);
            }
            if (!$siteId) {
                $site = array_filter(
                    static::getSites(),
                    function($v) {
                        return $v['DEF'] === 'Y';
                    }
                );
                if ($site) {
                    $siteId = current($site)['ID'] ?? '';
                }
            }
            self::$siteId = $siteId;
        }

        return self::$siteId;
    }

    /**
     * @param array $aGlobalMenu
     * @param array $aModuleMenu
     * @return void
     */
    public static function DoBuildGlobalMenu(array &$aGlobalMenu, array &$aModuleMenu)
    {
//        $aModuleMenu[] = [
//            "parent_menu" => "global_menu_settings",
//            "icon" => "dev2fun_compressimage_menu_icon",
//            "page_icon" => "dev2fun_compressimage_page_icon",
//            "sort" => "900",
//            "text" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TEXT"),
//            "title" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TITLE"),
//            "url" => "/bitrix/admin/dev2fun_imagecompress_files.php",
//            "items_id" => "menu_dev2fun_compressimage",
//            "section" => "dev2fun_imagecompress",
//            "more_url" => [],
//            //            "items" => array(
//            //                array(
//            //                    "text" => GetMessage("DEV2FUN_IMAGECOMPRESS_SUB_SETINGS_MENU_TEXT"),
//            //                    "title"=> GetMessage("DEV2FUN_IMAGECOMPRESS_SUB_SETINGS_MENU_TITLE"),
//            //                    "url"=>"/bitrix/admin/dev2fun_imagecompress_files.php",
//            //                    "sort"=>"100",
//            //                    "icon" => "sys_menu_icon",
//            //                    "page_icon" => "default_page_icon",
//            //                ),
//            //            )
//        ];
    }

    /**
     * @return void
     */
    public static function ShowThanksNotice()
    {
        \CAdminNotify::Add([
            'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage(
                'D2F_IMAGECOMPRESS_DONATE_MESSAGE',
                ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.imagecompress&mid_menu=1&tabControl_active_tab=donate']
            ),
            'TAG' => 'dev2fun_imagecompress_update',
            'MODULE_ID' => self::MODULE_ID,
        ]);
    }
}