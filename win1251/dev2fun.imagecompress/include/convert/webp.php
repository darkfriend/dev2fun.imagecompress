<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 24.04.2020
 * Time: 1:06
 */
?>
<?php
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
        <input type="checkbox"
               name="enable_<?=$optType?>"
               id="enable_<?=$optType?>"
               value="Y"
            <?php
            if (Option::get($curModuleName, "enable_{$optType}") === 'Y') {
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
        <select name="opti_algorithm_<?=$optType?>">
            <?php
            $selectAlgorithm = Option::get($curModuleName, "opti_algorithm_{$optType}");
            foreach ($optiAlgorithmList[$optType] as $v) { ?>
                <option value="<?= $v ?>" <?= ($v == $selectAlgorithm ? 'selected' : '') ?>><?= $v ?></option>
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
               name="path_to_<?=$optType?>"
               value="<?= Option::get($curModuleName, "path_to_{$optType}", '/usr/bin'); ?>"
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
        <select name="webp[string][webp_quality]">
            <?php
            $quality = Option::get($curModuleName, "webp_quality", 90);
            for ($i = 100; $i >= 50; $i -= 5) { ?>
                <option value="<?= $i ?>" <?= ($i == $quality ? 'selected' : '') ?>><?= $i ?></option>
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
        <select name="webp[string][webp_compress]">
            <?php
            $compress = Option::get($curModuleName, "webp_compress", 4);
            for ($i = 0; $i <= 6; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $compress ? 'selected' : '') ?>><?= $i ?></option>
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
        <input type="checkbox"
               name="webp[checkbox][webp_multithreading]"
               value="Y"
            <?php
            if (Option::get($curModuleName, "webp_multithreading") == 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>
