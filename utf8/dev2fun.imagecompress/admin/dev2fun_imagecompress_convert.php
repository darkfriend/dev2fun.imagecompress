<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Dev2fun\ImageCompress\AdminList;
use Bitrix\Main\Localization\Loc;
use Dev2fun\ImageCompress\Convert;
use Dev2fun\ImageCompress\ImageCompressImagesTable;

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
if (!$canRead && !$canWrite) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}


$EDITION_RIGHT = $APPLICATION->GetGroupRight($curModuleName);
if ($EDITION_RIGHT === "D") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$aTabs = [
    [
        "DIV" => "main",
        "TAB" => Loc::getMessage("SEC_MAIN_TAB"),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage("SEC_MAIN_TAB_TITLE"),
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$bVarsFromForm = false;
$APPLICATION->SetTitle(Loc::getMessage("DEV2FUN_IMAGECOMPRESS_CONVERT_TITLE"));

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$recCompress = null;
if (!empty($_REQUEST["convert"]) || ($_REQUEST["action"] ?? '') === "convert") {

    if ($_REQUEST["convert"]) {
        $imagesId = (array)$_REQUEST["convert"];
    } elseif ($_REQUEST["action"] === "convert") {
        $imagesId = (array)($_REQUEST["ID"] ?? []);
    }

    if ($imagesId) {
        $arImages = \Dev2fun\ImageCompress\ImageCompressImagesTable::getList([
            'select' => [
                '*',
                'CONVERTED_IMAGE_PATH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.IMAGE_PATH',
                'CONVERTED_IMAGE_ID' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ID',
                'CONVERTED_IMAGE_HASH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ORIGINAL_IMAGE_HASH',
                'CONVERTED_IMAGE_PROCESSED' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED',

                'REF_IMAGE_ID' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.ID',
            ],
            'filter' => [
                'ID' => $imagesId,
            ],
        ])->fetchAll();

        if ($arImages) {
            foreach ($arImages as $k => &$arFile) {
                $pathFile = Convert::getNormalizePathFile($arFile['IMAGE_PATH']);
                if ($pathFile === null) {
                    ImageCompressImagesTable::update($arFile['ID'], [
                        'IMAGE_IGNORE' => 'Y',
                        'DATE_UPDATE' => new SqlExpression("NOW()"),
                        'DATE_CHECK' => new SqlExpression("NOW()"),
                    ]);
                    unset($arImages[$k]);
                    continue;
                }

                if ($arFile['IMAGE_PATH'] !== $pathFile) {
                    $arFile['IMAGE_PATH'] = $pathFile;
                }

                if (empty($arFile['IMAGE_HASH'])) {
                    $absPath = $_SERVER['DOCUMENT_ROOT'] . $arFile['IMAGE_PATH'];
                    if (is_file($absPath)) {
                        $imageHash = md5_file($absPath);
                        ImageCompressImagesTable::update($arFile['ID'], [
                            'IMAGE_HASH' => $imageHash,
                        ]);
                        $arFile['IMAGE_HASH'] = $imageHash;
                    } else {
                        ImageCompressImagesTable::update($arFile['ID'], [
                            'IMAGE_IGNORE' => 'Y',
                            'DATE_UPDATE' => new SqlExpression("NOW()"),
                            'DATE_CHECK' => new SqlExpression("NOW()"),
                        ]);
                        unset($arImages[$k]);
                        continue;
                    }
                }
            }
            unset($arFile);
            \Dev2fun\ImageCompress\LazyConvert::convertItems($arImages);
        }
        $recCompress = true;
    }
}

$list = new AdminList($curModuleName);

$list->generalKey = 'ID';
$list->setRights();
$list->setTitle(Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONVERT_TITLE'));

//$list->setContextMenu(false);
$list->getlAdmin()->AddAdminContextMenu([
    'convert_all' => [
        'TEXT' => Loc::getMessage(
            'DEV2FUN_IMAGECOMPRESS_CONVERT_ALL',
            [
                '#IMAGE_TYPE#' => Convert::getInstance()->getImageTypeByAlgorithm(Convert::getInstance()->algorithm)
            ]
        ),
        'LINK' => $APPLICATION->GetCurPage() . '?convert_all=Y',
    ],
]);
$list->setHeaders([
    'ID' => "ID",
    'IMAGE_IGNORE' => "Ignore",
    'IMAGE' => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_IMAGE"),
    'IMAGE_HASH' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_IMAGE_HASH'),
//    'SITE_ID' => GetMessage('DEV2FUN_IMAGECOMPRESS_SITE_ID'),
    'IMAGE_PATH' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_IMAGE_PATH'),
    'CONVERTED_IMAGE_PATH' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONVERTED_IMAGE_PATH'),
    'CONVERTED_IMAGE_PROCESSED' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_IMAGE_PROCESSED'),
    'DATE_UPDATE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_DATE_UPDATE'),
    'DATE_CHECK' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_DATE_CHECK'),
]);
$list->setFilter([
    'ID' => ['TITLE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_FILTER_ID'), 'OPER' => ''],
//    'SITE_ID' => ['TITLE' => GetMessage('DEV2FUN_IMAGECOMPRESS_FILTER_SITE_ID'), 'OPER' => ''],
    'CONVERTED_IMAGE_PROCESSED' => [
        'TITLE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_IMAGE_PROCESSED'),
        'TYPE' => 'select',
        'OPER' => '',
        'VARIANTS' => [
            'Y' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_FILTER_Y'),
            'N' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_FILTER_N'),
        ],
    ],
//    'DATE_CHECK' => [
//        'TITLE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_DATE_CHECK'),
//        'TYPE' => 'calendar',
//        'OPER' => '',
////        'VARIANTS' => [
////            'Y' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_FILTER_Y'),
////            'N' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_FILTER_N'),
////        ],
//    ],
    'CONVERTED_IMAGE_TYPE' => [
        'TITLE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONVERTED_IMAGE_TYPE'),
        'TYPE' => 'select',
        'OPER' => '',
        'VARIANTS' => [
            \Dev2fun\ImageCompress\Convert::TYPE_WEBP => \Dev2fun\ImageCompress\Convert::TYPE_WEBP,
            \Dev2fun\ImageCompress\Convert::TYPE_AVIF => \Dev2fun\ImageCompress\Convert::TYPE_AVIF,
        ],
    ],
    'IMAGE_PATH' => [
        'TITLE' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_IMAGE_PATH'),
        'OPER' => '',
    ],
]);
if (!isset($by)) {
    $by = 'ID';
}
if (!isset($order)) {
    $order = 'ASC';
}

$rsFiles = \Dev2fun\ImageCompress\ImageCompressImagesTable::getList([
    'select' => [
        '*',
        'CONVERTED_IMAGE_PATH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.IMAGE_PATH',
        'CONVERTED_IMAGE_ID' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ID',
        'CONVERTED_IMAGE_HASH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ORIGINAL_IMAGE_HASH',
        'CONVERTED_IMAGE_PROCESSED' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED',
        'CONVERTED_IMAGE_TYPE' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE',
    ],
    'filter' => $list->makeFilter(),
    'order' => [$by => $order],
]);

//$list->setContextMenu();
if ($rsFiles->getSelectedRowsCount()>0) {
    $list->setList(
        $rsFiles,
        [
            'IMAGE' => function ($val, $arRec)
            {
                $path = $_SERVER['DOCUMENT_ROOT'] . $arRec['IMAGE_PATH'];
                if (file_exists($path)) {
                    $name = basename($path);
                    $mimeType = mime_content_type($path);
                    if (strpos($mimeType, 'image') !== false) {
                        if ($arRec['CONVERTED_IMAGE_PROCESSED'] === 'Y') {
                            $btnText = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_RECONVERT_BTN_ACTION');
                        } else {
                            $btnText = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONVERT_BTN_ACTION');
                        }
                        return "
                        <button name='convert' value='{$arRec['ID']}' data-image-id='{$arRec['ID']}'>{$btnText}</button>
                        <br>
                        <img style='max-width: 200px; height: auto;' src='{$arRec['IMAGE_PATH']}'>
                    ";
                    } else {
                        return "<a href=\"{$arRec['IMAGE_PATH']}\" target='_blank'>{$name}</a>";
                    }
                } else {
                    return "<span class='text-error'>" . Loc::getMessage(
                            'DEV2FUN_IMAGECOMPRESS_FILE_NOT_FOUND'
                        ) . "</span>";
                }
            },
        ],
        [
            'edit' => false,
            'delete' => false,
        ]
    );
}
$list->setFooter([
    'convert' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONVERT_BTN_ACTION'),
]);
$list->output('convert');
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
