<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.4.0
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Dev2fun\ImageCompress\AdminList;
use Dev2fun\ImageCompress\Compress;
use Bitrix\Main\Localization\Loc;

$curModuleName = "dev2fun.imagecompress";
Loader::includeModule($curModuleName);
//CModule::IncludeModule($curModuleName);

IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('imagecompress_list_read');
$canWrite = $USER->CanDoOperation('imagecompress_list_write');
if (!$canRead && !$canWrite)
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$EDITION_RIGHT = $APPLICATION->GetGroupRight($curModuleName);
if ($EDITION_RIGHT == "D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = [
    [
        "DIV" => "main",
        "TAB" => GetMessage("SEC_MAIN_TAB"),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage("SEC_MAIN_TAB_TITLE"),
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$bVarsFromForm = false;
$APPLICATION->SetTitle(GetMessage("SEC_IMG_COMPRESS_TITLE"));

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$recCompress = null;
if ($_REQUEST["compress"]) {

    if ($compress = $_REQUEST["compress"]) {
        if (!is_array($compress)) {
            $compress = [$compress];
        }
        foreach ($compress as $fileID) {
            $fileID = intval($fileID);
            $recCompress = Compress::getInstance()->compressImageByID($fileID);
        }
    }

} elseif ($_REQUEST["action"] == "compress") {

    $arIDs = $_REQUEST["ID"];
    if (is_array($arIDs) && count($arIDs)) {
        set_time_limit(0);
        foreach ($arIDs as $fileID) {
            $recCompress = Compress::getInstance()->compressImageByID($fileID);
        }
    }

} elseif (!empty($_REQUEST['compress_file_delete'])) {
    CFile::Delete(intval($_REQUEST["compress_file_delete"]));
}

$list = new AdminList($curModuleName);
$list->generalKey = 'ID';
$list->SetRights();
$list->SetTitle(GetMessage('DEV2FUN_IMAGECOMPRESS_TITLE'));
$list->SetGroupAction([
    'compress' => function ($hash)
    {
    },
]);
$list->SetContextMenu(false);
$list->SetHeaders([
    'ID' => "ID",
    'MODULE_ID' => GetMessage('DEV2FUN_IMAGECOMPRESS_MODULE_ID'),
    'CONTENT_TYPE' => GetMessage("DEV2FUN_IMAGECOMPRESS_CONTENT_TYPE"),
    'FILE_NAME' => GetMessage("DEV2FUN_IMAGECOMPRESS_FILE_NAME"),
    'ORIGINAL_NAME' => GetMessage("DEV2FUN_IMAGECOMPRESS_ORIGINAL_NAME"),
    'DESCRIPTION' => GetMessage("DEV2FUN_IMAGECOMPRESS_DESCRIPTION"),
    'FILE_SIZE' => GetMessage("DEV2FUN_IMAGECOMPRESS_FILE_SIZE"),
    'IMAGE' => GetMessage("DEV2FUN_IMAGECOMPRESS_IMAGE"),
    'COMPRESS' => GetMessage("DEV2FUN_IMAGECOMPRESS_COMPRESS"),
    'SIZE_BEFORE' => GetMessage("DEV2FUN_IMAGECOMPRESS_SIZE_BEFORE"),
    'SIZE_AFTER' => GetMessage("DEV2FUN_IMAGECOMPRESS_SIZE_AFTER"),
]);
$list->SetFilter([
    'id' => ['TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_ID'), 'OPER' => ''],
    'file_size' => [
        'TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_FILE_SIZE'),
    ],
    'comressed' => [
        'TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_COMRESSED'),
        'TYPE' => 'select',
        'VARIANTS' => [
            "Y" => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_COMRESSED_Y'),
            "N" => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_COMRESSED_N'),
        ],
    ],
    'module_id' => ['TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_MODULE_ID')],
    'original_name' => ['TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_ORIGINAL_NAME'), 'OPER' => ''],
    'file_name' => ['TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_FILE_NAME'), 'OPER' => ''],
    'content_type' => [
        'TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_FILE_TYPE'),
        'TYPE' => 'select',
        'OPER' => '@',
        'VARIANTS' => [
            'image/png' => GetMessage('DEV2FUN_IMAGECOMPRESS_MIME_PNG'),
            'image/jpeg' => GetMessage('DEV2FUN_IMAGECOMPRESS_MIME_JPG'),
            'application/pdf' => GetMessage('DEV2FUN_IMAGECOMPRESS_MIME_PDF'),
            'image/svg' => GetMessage('DEV2FUN_IMAGECOMPRESS_MIME_SVG'),
            'image/gif' => GetMessage('DEV2FUN_IMAGECOMPRESS_MIME_GIF'),
        ],
    ],
]);
if (!isset($by))
    $by = 'ID';
if (!isset($order))
    $order = 'ASC';

$rsFiles = Compress::getInstance()->getFileList([$by => $order], $list->makeFilter());

$list->SetList(
    $rsFiles,
    [
        'IMAGE' => function ($val, $arRec)
        {
            $arFile = \CFile::GetFileArray($arRec["ID"]);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC'])) {
                $mimeType = mime_content_type($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC']);
                if($mimeType==='application/pdf') {
                    return "<a href=\"{$arFile['SRC']}\" target='_blank'>{$arFile['ORIGINAL_NAME']}</a>";
                } else {
                    return "<img style='max-width: 200px; height: auto;' src='" . $arFile['SRC'] . "'>";
                }
            } else {
                return "<span class='text-error'>" . GetMessage('DEV2FUN_IMAGECOMPRESS_FILE_NOT_FOUND') . "</span>";
            }
        },
        'COMPRESS' => function ($val, $arRec)
        {
            $strFilePath = \CFile::GetPath($arRec["ID"]);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $strFilePath)) {
                if (intval($arRec['FILE_ID']) <= 0) {
                    return "<button value='" . $arRec["ID"] . "' name='compress' data-image-id='" . $arRec["ID"] . "'>" . GetMessage("DEV2FUN_IMAGECOMPRESS_COMPRESS") . "</button>";
                } else {
                    return GetMessage('DEV2FUN_IMAGECOMPRESS_COMRESSED')
                        . '<br>'
                        . "<button value='" . $arRec["ID"] . "' name='compress' data-image-id='" . $arRec["ID"] . "'>" . GetMessage("DEV2FUN_IMAGECOMPRESS_COMPRESS_REPEAT") . "</button>";
                }
            } else {
                $labelBtnDelete = GetMessage("DEV2FUN_IMAGECOMPRESS_FILE_DELETE");
                return "<span class='text-error'>" . GetMessage('DEV2FUN_IMAGECOMPRESS_FILE_NOT_FOUND') . "</span><br><button value='{$arRec["ID"]}' name='compress_file_delete' data-image-id='{$arRec["ID"]}'>$labelBtnDelete</button>";
            }
        },
        'SIZE_BEFORE' => function ($val, $arRec)
        {
            return Compress::getInstance()->getNiceFileSize($arRec["SIZE_BEFORE"]);
        },
        'SIZE_AFTER' => function ($val, $arRec)
        {
            return Compress::getInstance()->getNiceFileSize($arRec["SIZE_AFTER"]);
        },
        'FILE_SIZE' => function ($val, $arRec)
        {
            return Compress::getInstance()->getNiceFileSize($arRec["FILE_SIZE"]);
        },
    ],
    false
);
$list->SetFooter([
    'compress' => GetMessage('DEV2FUN_IMAGECOMPRESS_COMPRESS'),
]);
$list->Output();
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
