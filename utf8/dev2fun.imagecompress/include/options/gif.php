<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.5.0
 */

/**
 * @var string $optType
 */
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
?>
<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', ['#MODULE#' => $optType]) ?></b>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="enable_<?=$optType?>">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_OPTIMIZE_TO", ['#MODULE#' => $optType]) ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="checkbox"
            name="options[<?=$arSite['ID']?>][enable_<?=$optType?>]"
            value="Y"
            <?php
            if (Option::get($curModuleName, "enable_{$optType}", 'N', $arSite['ID']) === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>
<tr>
    <td width="40%">
        <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_SELECT') ?>:</label>
    </td>
    <td width="60%">
        <select name="options[<?=$arSite['ID']?>][opti_algorithm_<?=$optType?>]">
            <?php
            $selectAlgorithm = Option::get($curModuleName, "opti_algorithm_{$optType}", '', $arSite['ID']);
            foreach ($optiAlgorithmList[$optType] as $v) { ?>
                <option value="<?= $v ?>" <?= ($v == $selectAlgorithm ? 'selected' : '') ?>>
                    <?= $v ?>
                </option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="path_to_<?=$optType?>">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_TO",['#MODULE#'=>$optType]) ?>:
        </label>
    </td>
    <td width="60%">
        <input type="text"
               size="50"
               name="options[<?=$arSite['ID']?>][path_to_<?=$optType?>]"
               value="<?= Option::get($curModuleName, "path_to_{$optType}", '/usr/bin', $arSite['ID']); ?>"
        /> /gifsicle
    </td>
</tr>


<tr>
    <td width="40%">
        <label for="gif[string][gif_compress]">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_GIF_COMPRESS") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="options[<?=$arSite['ID']?>][gif][string][gif_compress]">
            <?php
            $quality = Option::get($curModuleName, "gif_compress", 2, $arSite['ID']);
            for ($i = 1; $i <= 3; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $quality ? 'selected' : '') ?>><?= $i ?></option>
            <?php } ?>
        </select>
    </td>
</tr>
