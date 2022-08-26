<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.1
 */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

$tabControl->BeginNextTab();
$convertAlgorithm = \array_keys(\Dev2fun\ImageCompress\Convert::$convertClasses);
$convertModes = \Dev2fun\ImageCompress\Convert::$convertModes;
?>
<script type="text/javascript">
    <?=file_get_contents(__DIR__.'/../install/js/script.js');?>
</script>

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
        <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_MODE_CONVERT') ?>:</label>
    </td>
    <td width="60%">
        <select name="convert_mode[]" multiple>
            <?php
            $selectConvertMode = \Dev2fun\ImageCompress\Convert::getInstance()->convertMode;
            foreach ($convertModes as $v) { ?>
                <option
                    value="<?=$v?>"
                    <?= \in_array($v, $selectConvertMode) ? 'selected' : '' ?>
                >
                    <?=$v?>
                </option>
            <?php } ?>
        </select>
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
        <label for="convert_quality">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_WEBP_QUALITY") ?>:
        </label>
    </td>
    <td width="60%">
        <select name="convert_quality">
            <?php
            $webpQuality = Option::get($curModuleName, 'convert_quality', 80);
            if(!$webpQuality) $webpQuality = 80;
            for ($i = 60; $i <= 100; $i += 1) { ?>
                <option value="<?= $i ?>" <?= ($i == $webpQuality ? 'selected' : '') ?>><?= $i ?></option>
            <?php } ?>
        </select>
    </td>
</tr>

<tr>
    <td width="40%" class="adm-detail-content-cell-l">
        <label><?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PAGE_EXCLUDED"); ?>:</label>
    </td>
    <td width="60%" class="adm-detail-content-cell-r">
        <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%"
               id="d2f_page_excluded_webp">
            <tbody>
            <?php
            $excludedPages = \Dev2fun\ImageCompress\Convert::getSettingsExcludePage();
            $serverUrl = \Dev2funImageCompress::getUrl('/');
            foreach ($excludedPages as $key => $page) {
                $key = \str_replace('n', '', $key);
                ?>
                <tr>
                    <td>
                        <label><?= $serverUrl ?></label>
                        <input
                            name="EXCLUDE_PAGES[n<?= $key ?>]"
                            value="<?= $page ?>"
                            size="30"
                            type="text"
                        >
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td>
                    <label><?= $serverUrl ?></label>
                    <input
                        name="EXCLUDE_PAGES[n<?= count($excludedPages) ?>]"
                        value=""
                        size="30"
                        type="text"
                    >
                </td>
            </tr>
            <tr>
                <td>
                    <input
                        type="button"
                        value="<?= Loc::getMessage("LABEL_ADD"); ?>"
                        onclick="addNewRow('d2f_page_excluded_webp')"
                    >
                </td>
            </tr>
            <script type="text/javascript">
                BX.addCustomEvent('onAutoSaveRestore', function (ob, data) {
                    for (var i in data) {
                        if (i.substring(0, 9) == 'EXCLUDE_PAGES[') {
                            addNewRow('d2f_page_excluded_webp')
                        }
                    }
                });
            </script>
            </tbody>
        </table>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <?php
        echo BeginNote();
        echo Loc::getMessage('D2F_COMPRESS_PAGE_EXCLUDED_TEXT');
        EndNote();
        ?>
    </td>
</tr>

<tr>
    <td width="40%" class="adm-detail-content-cell-l">
        <label><?= Loc::getMessage("D2F_COMPRESS_REFERENCES_FILE_EXCLUDED"); ?>:</label>
    </td>
    <td width="60%" class="adm-detail-content-cell-r">
        <table class="nopadding" cellpadding="0" cellspacing="0" border="0" width="100%"
               id="d2f_file_excluded_webp">
            <tbody>
            <?php
            $excludedPages = \Dev2fun\ImageCompress\Convert::getSettingsExcludeFiles();
            $serverUrl = \Dev2funImageCompress::getUrl('/');
            foreach ($excludedPages as $key => $page) {
                $key = \str_replace('n', '', $key);
                ?>
                <tr>
                    <td>
                        <label><?= $serverUrl ?></label>
                        <input
                            name="EXCLUDE_FILES[n<?= $key ?>]"
                            value="<?= $page ?>"
                            size="30"
                            type="text"
                        >
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td>
                    <label><?= $serverUrl ?></label>
                    <input
                        name="EXCLUDE_FILES[n<?= count($excludedPages) ?>]"
                        value=""
                        size="30"
                        type="text"
                    >
                </td>
            </tr>
            <tr>
                <td>
                    <input
                        type="button"
                        value="<?= Loc::getMessage("LABEL_ADD"); ?>"
                        onclick="addNewRow('d2f_file_excluded_webp')"
                    >
                </td>
            </tr>
            <script type="text/javascript">
                BX.addCustomEvent('onAutoSaveRestore', function (ob, data) {
                    for (var i in data) {
                        if (i.substring(0, 9) == 'EXCLUDE_FILES[') {
                            addNewRow('d2f_file_excluded_webp')
                        }
                    }
                });
            </script>
            </tbody>
        </table>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <?php
        echo BeginNote();
        echo Loc::getMessage('D2F_COMPRESS_FILE_EXCLUDED_TEXT');
        EndNote();
        ?>
    </td>
</tr>

<?php /* ?>
<tr>
    <td width="40%">
        <label for="cache_time">
            <?= Loc::getMessage("D2F_IMAGECOMPRESS_HEADING_TEXT_POST_CONVERT_CACHE_TIME") ?>:
        </label>
    </td>
    <td width="60%">
        <?php
        $cacheTime = Option::get($curModuleName, 'cache_time', 3600);
        ?>
        <input name="cache_time" value="<?= $cacheTime ?>" size="50" type="text"><br>
    </td>
</tr>
 <?php */ ?>


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