<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.2.1
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
if(!check_bitrix_sessid()) return;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

Loader::includeModule('main');

CAdminMessage::ShowMessage(array(
    "MESSAGE"=>Loc::getMessage('D2F_IMAGECOMPRESS_INSTALL_SUCCESS'),
    "TYPE"=>"OK"
));
echo BeginNote();
echo Loc::getMessage("D2F_IMAGECOMPRESS_INSTALL_LAST_MSG");
echo EndNote();