<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.2
 */

if (!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("main");

use \Bitrix\Main\Localization\Loc;

?>
<form action="<?= $APPLICATION->GetCurPageParam('UNSTEP=2', ['UNSTEP']) ?>" method="post">
    <?= bitrix_sessid_post() ?>
    <table width="400" border="0" class="table">
        <tr>
            <td></td>
            <td>
                <label>
                    <input type="checkbox" name="D2F_UNSTEP_FIELDS[DB]" value="Y">
                    <?= Loc::getMessage('D2F_IMAGECOMPRESS_UNINSTALL_DB') ?>
                </label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="UNSTEP" value="2">
                <input type="submit" name="next"
                       value="<?= Loc::getMessage('D2F_COMPRESS_REFERENCES_GOTO_UNINSTALL') ?>">
            </td>
        </tr>
    </table>
</form>
