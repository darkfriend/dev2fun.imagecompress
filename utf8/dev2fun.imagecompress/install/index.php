<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.9.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

use Bitrix\Main\ModuleManager,
    Bitrix\Main\EventManager,
    Dev2fun\ImageCompress\ImageCompressTable,
    Dev2fun\ImageCompress\ImageCompressImagesTable,
    Dev2fun\ImageCompress\ImageCompressImagesConvertedTable,
    Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\IO\Directory,
    Bitrix\Main\Config\Option;

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
        "Dev2funImageCompress" => 'include.php',

        "Dev2fun\ImageCompress\Jpegoptim" => 'lib/Jpegoptim.php',
        "Dev2fun\ImageCompress\Optipng" => 'lib/Optipng.php',
        "Dev2fun\ImageCompress\Cache" => 'lib/Cache.php',
    ]
);

class dev2fun_imagecompress extends CModule
{
    var $MODULE_ID = "dev2fun.imagecompress";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $path = \str_replace("\\", "/", __FILE__);
        $path = \substr($path, 0, \strlen($path) - \strlen("/index.php"));
        include($path . "/version.php");
        if (isset($arModuleVersion) && \is_array($arModuleVersion) && \array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = '0.7.3';
            $this->MODULE_VERSION_DATE = '2023-08-06 00:10:00';
        }
        $this->MODULE_NAME = Loc::getMessage("D2F_MODULE_NAME_IMAGECOMPRESS");
        $this->MODULE_DESCRIPTION = Loc::getMessage("D2F_MODULE_DESCRIPTION_IMAGECOMPRESS");

        $this->PARTNER_NAME = "dev2fun";
        $this->PARTNER_URI = "http://dev2fun.com/";
    }

    /**
     * @return false|void
     */
    public function DoInstall()
    {
        global $APPLICATION;
        //        ini_set('display_errors',true);
        if (!check_bitrix_sessid()) return false;
        try {
//            if ($_REQUEST['STEP'] == 1 || !$_REQUEST['D2F_FIELDS']) {
//                $APPLICATION->IncludeAdminFile(
//                    Loc::getMessage("D2F_MODULE_IMAGECOMPRESS_STEP1"),
//                    __DIR__ . "/step1.php"
//                );
//            } else {
            $this->saveFields();
//                $this->check();
            $this->installFiles();
            $this->installDB();
            $this->registerEvents();

            ModuleManager::registerModule($this->MODULE_ID);

            $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 60, 'FULL');
            $agentId = CAgent::AddAgent(
                \Dev2fun\ImageCompress\LazyConvert::class . '::agentRun();',
                $this->MODULE_ID,
                'Y',
                60,
                '',
                'Y',
                $startTime,
                100,
                false,
                false
            );
            if (!$agentId) {
                throw new Exception('Error when add agent for lazyConvert');
            }
            Option::set($this->MODULE_ID, 'convert_agent', $agentId);


            $startTime = ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 120, 'FULL');
            $agentId = CAgent::AddAgent(
                \Dev2fun\ImageCompress\Cache::class . '::agentRun();',
                $this->MODULE_ID,
                'Y',
                120,
                '',
                'N',
                $startTime,
                100,
                false,
                false
            );
            if (!$agentId) {
                throw new Exception('Error when add agent for cache-delete');
            }
            Option::set($this->MODULE_ID, 'cache_delete_agent', $agentId);

        } catch (Exception $e) {
            $GLOBALS['D2F_COMPRESSIMAGE_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_COMPRESSIMAGE_ERROR_NOTES'] = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("D2F_MODULE_IMAGECOMPRESS_STEP_ERROR"),
                __DIR__ . "/error.php"
            );
            return false;
        }
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("D2F_MODULE_IMAGECOMPRESS_STEP_FINAL"),
            __DIR__ . "/final.php"
        );
    }

    /**
     * @return void
     */
    public function DoUninstall()
    {
        global $APPLICATION;
//        $request = Application::getInstance()->getContext()->getRequest();
//        ModuleManager::unRegisterModule($this->MODULE_ID);
        if (!check_bitrix_sessid()) {
            return;
        }
        if (empty($_REQUEST['UNSTEP']) || $_REQUEST['UNSTEP'] == 1) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("D2F_MODULE_IMAGECOMPRESS_UNSTEP1"),
                __DIR__ . "/unstep1.php"
            );
        } else {
            $this->deleteFiles();
            if (!empty($_REQUEST['D2F_UNSTEP_FIELDS']['DB'])) {
                $this->unInstallDB();
            }

            $this->unRegisterEvents();

            CAgent::RemoveModuleAgents($this->MODULE_ID);

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $admMsg = new CAdminMessage(false);
            $admMsg->ShowMessage([
                "MESSAGE" => Loc::getMessage('D2F_IMAGECOMPRESS_UNINSTALL_SUCCESS'),
                "TYPE" => "OK",
            ]);
            echo BeginNote();
            echo Loc::getMessage("D2F_IMAGECOMPRESS_UNINSTALL_LAST_MSG");
            echo EndNote();
        }
    }

    /**
     * @return void
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function saveFields()
    {
        $pth = '/usr/bin';
        Option::set($this->MODULE_ID, 'path_to_jpegoptim', $pth);
        Option::set($this->MODULE_ID, 'opti_algorithm_jpeg', 'jpegoptim');

        Option::set($this->MODULE_ID, 'path_to_optipng', $pth);
        Option::set($this->MODULE_ID, 'opti_algorithm_png', 'optipng');
    }

    /**
     * @deprecated
     * @throws Exception
     */
    public function check()
    {
        if (!Dev2fun\ImageCompress\Check::isJPEGOptim('jpegoptim')) {
            throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
        }

        if (!Dev2fun\ImageCompress\Check::isPNGOptim('optipng')) {
            throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'optipng']));
        }
    }

    /**
     * @return true
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     */
    public function installDB()
    {
        if (!ImageCompressTable::getEntity()->getConnection()->isTableExists(ImageCompressTable::getTableName())) {
            ImageCompressTable::getEntity()->createDbTable();
        }
        if (!ImageCompressImagesTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesTable::getTableName())) {
            ImageCompressImagesTable::createTable();
        }
        if (!ImageCompressImagesConvertedTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesConvertedTable::getTableName())) {
            ImageCompressImagesConvertedTable::createTable();
        }
        if (!ImageCompressImagesToConvertedTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesToConvertedTable::getTableName())) {
            ImageCompressImagesToConvertedTable::createTable();
        }

        Option::set($this->MODULE_ID, 'enable_element', 'Y');
        Option::set($this->MODULE_ID, 'enable_section', 'Y');
        Option::set($this->MODULE_ID, 'enable_resize', 'Y');
        Option::set($this->MODULE_ID, 'enable_save', 'Y');

        Option::set($this->MODULE_ID, 'jpegoptim_compress', '80');
        Option::set($this->MODULE_ID, 'jpeg_progressive', 'Y');
        Option::set($this->MODULE_ID, 'optipng_compress', '3');

        $sites = Dev2funImageCompress::getSites();
        foreach ($sites as $site) {
            Option::set(
                $this->MODULE_ID,
                'convert_mode',
                \serialize(['hitConvert', 'postConvert']),
                $site['ID']
            );
        }

        return true;
    }

    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementAdd", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnElementEvent");
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementUpdate", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnElementEvent");

        $eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionAdd", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnSectionEvent");
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockSectionUpdate", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnSectionEvent");

        $eventManager->registerEventHandler("main", "OnFileSave", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnFileEvent");
        $eventManager->registerEventHandler("main", "OnFileDelete", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnFileDeleteEvent");

        $eventManager->registerEventHandler("main", "OnAfterResizeImage", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Compress", "CompressImageOnResizeEvent");

        $eventManager->registerEventHandler("main", "OnBuildGlobalMenu", $this->MODULE_ID, "Dev2funImageCompress", "DoBuildGlobalMenu");

        $eventManager->registerEventHandler("main", "OnGetFileSRC", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "CompressImageOnConvertEvent", 999);
        $eventManager->registerEventHandler("main", "OnAfterResizeImage", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "CompressImageCacheOnConvertEvent", 999);

        $eventManager->registerEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, "Dev2fun\\ImageCompress\\Convert", "PostConverterEvent", 999);

        return true;
    }

    public function installFiles()
    {
        CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
        CopyDirFiles(__DIR__ . "/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
        return true;
    }

    public function unInstallDB()
    {
        $connection = Application::getInstance()->getConnection();
//        $connection->dropTable(ImageCompressTable::getTableName());
        if (ImageCompressTable::getEntity()->getConnection()->isTableExists(ImageCompressTable::getTableName())) {
            $connection->dropTable(ImageCompressTable::getTableName());
        }
//        $connection->dropTable(ImageCompressPagesTable::getTableName());

        if (ImageCompressImagesTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesTable::getTableName())) {
            ImageCompressImagesTable::dropTable();
        }
        if (ImageCompressImagesConvertedTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesConvertedTable::getTableName())) {
            ImageCompressImagesConvertedTable::dropTable();
        }
        if (ImageCompressImagesToConvertedTable::getEntity()->getConnection()->isTableExists(ImageCompressImagesToConvertedTable::getTableName())) {
            ImageCompressImagesToConvertedTable::dropTable();
        }

        Option::delete($this->MODULE_ID);
        return true;
    }

    public function deleteFiles()
    {
        DeleteDirFilesEx('/bitrix/admin/dev2fun_imagecompress_files.php');
        DeleteDirFilesEx('/bitrix/admin/dev2fun_imagecompress_convert.php');
        DeleteDirFilesEx('/bitrix/themes/.default/icons/dev2fun.imagecompress');
        DeleteDirFilesEx('/bitrix/themes/.default/dev2fun.imagecompress.css');
        return true;
    }

    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler('main', 'OnFileSave', $this->MODULE_ID);
        $eventManager->unRegisterEventHandler('main', 'OnFileDelete', $this->MODULE_ID);

        $eventManager->unRegisterEventHandler('main', 'OnAfterResizeImage', $this->MODULE_ID);

        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockSectionUpdate', $this->MODULE_ID);
        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockSectionAdd', $this->MODULE_ID);

        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID);
        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID);

        $eventManager->unRegisterEventHandler('main', 'OnBuildGlobalMenu', $this->MODULE_ID);

        $eventManager->unRegisterEventHandler('main', 'OnGetFileSRC', $this->MODULE_ID);
        $eventManager->unRegisterEventHandler('main', 'OnAfterResizeImage', $this->MODULE_ID);
        $eventManager->unRegisterEventHandler('main', 'OnEndBufferContent', $this->MODULE_ID);

        return true;
    }
}
