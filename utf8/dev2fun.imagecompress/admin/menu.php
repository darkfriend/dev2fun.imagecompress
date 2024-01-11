<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 */

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Localization\Loc;

if(!is_object($GLOBALS["USER_FIELD_MANAGER"])) {
    return false;
}

if(!$USER->CanDoOperation("dev2fun.imagecompress")) {
    return false;
}

$moduleId = 'dev2fun.imagecompress';
if (!\Bitrix\Main\Loader::includeModule($moduleId)) {
    return false;
}

$items[] = [
    'text' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_MENU_ITEM_FILES'),
    'url' => "dev2fun_imagecompress_files.php?lang=".LANGUAGE_ID,
    'module_id' => $moduleId,
    'items_id' => 'menu_dev2fun_imagecompress_files',
    'more_url' => [
        'dev2fun_imagecompress_files.php',
    ],
];
$items[] = [
    'text' => Loc::getMessage('DEV2FUN_IMAGECOMPRESS_MENU_ITEM_CONVERT'),
    'url' => "dev2fun_imagecompress_convert.php?lang=".LANGUAGE_ID,
    'module_id' => $moduleId,
    'items_id' => 'menu_dev2fun_imagecompress_convert',
    'more_url' => [
        'dev2fun_imagecompress_convert.php',
    ],
];

$arMenu = [
    "parent_menu" => "global_menu_settings",
    "section" => "dev2fun_imagecompress",
    "items_id" => "dev2fun_imagecompress",
//    "section" => "settings",
    "sort" => 900,
    "text" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TEXT"),
    "title" => Loc::getMessage("DEV2FUN_IMAGECOMPRESS_MENU_TITLE"),
    "url" => "dev2fun_imagecompress_files.php?lang=".LANGUAGE_ID,
    "more_url" => [
        "dev2fun_imagecompress_files.php",
        "dev2fun_imagecompress_convert.php",
    ],
    "icon" => "dev2fun_compressimage_menu_icon",
    "page_icon" => "dev2fun_compressimage_page_icon",
    "items" => $items,
];

return $arMenu;