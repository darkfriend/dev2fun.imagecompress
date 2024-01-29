<?php
/**
* @author dev2fun (darkfriend)
* @copyright darkfriend
* @version 0.8.0
*/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$path = \Bitrix\Main\Loader::getLocal('modules/dev2fun.imagecompress/admin/dev2fun_imagecompress_convert.php');
if (file_exists($path)) {
    include $path;
} else {
    ShowMessage('dev2fun_imagecompress_pages.php not found!');
}
