<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.5.0
 */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

$tabControl->BeginNextTab();
$convertAlgorithm = \array_keys(\Dev2fun\ImageCompress\Convert::$convertClasses);
?>

<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_BASE_CONVERT') ?></b>
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="enable_element">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_CONVERT") ?>:
        </label>
    </td>
    <td width="60%">
        <input type="checkbox"
               name="convert_enable"
               value="Y"
            <?php
            if (Option::get($curModuleName, "convert_enable") === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>

<tr>
    <td width="40%">
        <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_CONVERT') ?>:</label>
    </td>
    <td width="60%">
        <select name="convert_algorithm">
            <?php
            $selectAlgorithmConvert = Option::get($curModuleName, 'convert_algorithm', 'phpWebp');
            foreach ($convertAlgorithm as $v) { ?>
                <option
                    value="<?= $v ?>"
                    <?= ($v === $selectAlgorithmConvert ? 'selected' : '') ?>
                ><?= $v ?></option>
            <?php } ?>
        </select>
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="webp_quality">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_WEBP_QUALITY") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="webp_quality">
            <?php
            $webpQuality = Option::get($curModuleName, 'webp_quality', 80);
            if(!$webpQuality) $webpQuality = 80;
            for ($i = 60; $i <= 100; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $webpQuality ? 'selected' : '') ?>><?= $i ?></option>
            <?php } ?>
        </select>
    </td>
</tr>


<!--CWEBP-->
<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_CWEBP') ?></b>
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="path_to_cwebp">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_CWEBP") ?>:
        </label>
    </td>
    <td width="60%">
        <input type="text"
               size="50"
               name="path_to_cwebp"
               value="<?= Option::get($curModuleName, "path_to_cwebp", '/usr/bin'); ?>"
        /> /cwebp
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="cwebp_compress">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CWEBP_COMPRESS") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="cwebp_compress">
            <?php
            $cwebpCompress = Option::get($curModuleName, "cwebp_compress", 4);
            for ($i = 0; $i <= 6; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $cwebpCompress ? 'selected' : '') ?>><?= $i ?></option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="cwebp_multithreading">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CWEBP_MULTITHREADING") ?>:
        </label>
    </td>
    <td width="60%">
        <input type="checkbox"
               name="cwebp_multithreading"
               value="Y"
            <?php
            if (Option::get($curModuleName, 'cwebp_multithreading', 'Y') === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>