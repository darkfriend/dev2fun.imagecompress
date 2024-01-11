<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 24.04.2020
 * Time: 1:06
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
            id="enable_<?=$optType?>"
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
        <input
            type="text"
            size="50"
            name="options[<?=$arSite['ID']?>][path_to_<?=$optType?>]"
            value="<?= Option::get($curModuleName, "path_to_{$optType}", '/usr/bin', $arSite['ID']); ?>"
        /> /<?=$optType?>
    </td>
</tr>



<tr>
    <td width="40%">
        <label for="webp[string][webp_quality]">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_WEBP_QUALITY") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="options[<?=$arSite['ID']?>][webp][string][webp_quality]">
            <?php
            $quality = Option::get($curModuleName, "webp_quality", 90, $arSite['ID']);
            for ($i = 100; $i >= 50; $i -= 5) { ?>
                <option value="<?= $i ?>" <?= ($i == $quality ? 'selected' : '') ?>>
                    <?= $i ?>
                </option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="webp[string][compress]">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_WEBP_COMPRESS") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="options[<?=$arSite['ID']?>][webp][string][webp_compress]">
            <?php
            $compress = Option::get($curModuleName, "webp_compress", 4, $arSite['ID']);
            for ($i = 0; $i <= 6; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $compress ? 'selected' : '') ?>>
                    <?= $i ?>
                </option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="webp[checkbox][webp_multithreading]">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_WEBP_MULTITHREADING") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="checkbox"
            name="options[<?=$arSite['ID']?>][webp][checkbox][webp_multithreading]"
            value="Y"
            <?php
            if (Option::get($curModuleName, "webp_multithreading", '', $arSite['ID']) === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>
