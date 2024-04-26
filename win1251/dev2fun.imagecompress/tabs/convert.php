<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.9.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

/** @var array $arSites */
/** @var string $curModuleName */

$tabControl->BeginNextTab();
$convertAlgorithm = \array_keys(\Dev2fun\ImageCompress\Convert::$convertClasses);
$convertModes = \Dev2fun\ImageCompress\Convert::$convertModes;
$convertInstance = \Dev2fun\ImageCompress\Convert::getInstance();
?>

<?php if (
    \in_array(\Dev2fun\ImageCompress\Convert::LAZY_CONVERT, $convertInstance->convertMode)
    && (!defined('BX_CRONTAB_SUPPORT') || !BX_CRONTAB_SUPPORT)
) { ?>
    <tr>
        <td colspan="2">
            <?php
            echo BeginNote();
            echo Loc::getMessage('D2F_COMPRESS_CRONTAB_TEXT');
            echo EndNote();
            ?>
        </td>
    </tr>
<?php } ?>

<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage("D2F_COMPRESS_GLOBAL_SETTINGS") ?></b>
    </td>
</tr>

<!-- LAZY CONVERT -->
<tr class="convert__lazy_settings heading">
    <td colspan="2">
        <b><?=Loc::getMessage('D2F_COMPRESS_LAZY_HEADING_SETTINGS') ?></b>
    </td>
</tr>

<tr class="convert__lazy_settings">
    <td width="40%">
        <label for="convert_per_page">
            <?= Loc::getMessage("D2F_COMPRESS_LAZY_PER_PAGE") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            step="1"
            name="convert_per_page"
            value="<?=Option::get($curModuleName, "convert_per_page", 200);?>"
        />
    </td>
</tr>

<tr class="convert__lazy_settings">
    <td width="40%">
        <label for="convert_cache_time_find_images">
            <?= Loc::getMessage("D2F_COMPRESS_LAZY_CACHE_TIME_FIND") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            step="1"
            min="-1"
            name="convert_cache_time_find_images"
            value="<?=Option::get($curModuleName, "convert_cache_time_find_images", 3600*24)?>"
        />
    </td>
</tr>

<tr class="convert__lazy_settings">
    <td width="40%">
        <label for="convert_cache_time_get_images">
            <?= Loc::getMessage("D2F_COMPRESS_LAZY_CACHE_TIME_GET_IMAGES") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            step="1"
            min="-1"
            name="convert_cache_time_get_images"
            value="<?=Option::get($curModuleName, "convert_cache_time_get_images", 3600)?>"
        />
    </td>
</tr>

<tr class="convert__lazy_settings">
    <td width="40%">
        <label for="convert_cache_include_user_groups">
            <?= Loc::getMessage("D2F_COMPRESS_LAZY_USER_GROUPS") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="checkbox"
            name="convert_cache_include_user_groups"
            value="Y"
            <?php
            if (Option::get($curModuleName, 'convert_cache_include_user_groups', 'Y') === 'Y') {
                echo 'checked';
            }
            ?>
        />
    </td>
</tr>
<tr>
    <td></td>
    <td>
        <?php
        echo BeginNote();
        echo Loc::getMessage('D2F_COMPRESS_CONVERT_CACHE_INCLUDE_USER_GROUPS_TEXT');
        echo EndNote();
        ?>
    </td>
</tr>


<tr class="convert__lazy_settings heading">
    <td colspan="2">
        <b><?=Loc::getMessage('D2F_COMPRESS_CACHE_DELETE_HEADING') ?></b>
    </td>
</tr>
<tr class="convert__cache_delete_active">
    <td width="40%">
        <label for="cache_delete_active">
            <?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_ACTIVE") ?>:
        </label>
    </td>
    <td width="60%">
        <?php
        $cacheDeleteActive = \Dev2fun\ImageCompress\Cache::getAgentActiveValue() === 'Y';
        ?>
        <?php if ($cacheDeleteActive) { ?>
            <p><?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_AGENT_ACTIVATED") ?></p>
            <p>
                <input type="button" value="<?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_BTN_DEACTIVATE") ?>" onclick="cacheDeleteDeactivate();"/>
            </p>
        <?php } else { ?>
            <p><?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_AGENT_NOT_ACTIVATED") ?></p>
            <p>
                <input type="button" value="<?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_BTN_ACTIVATE") ?>" onclick="cacheDeleteActive();"/>
            </p>
        <?php } ?>
    </td>
</tr>
<tr class="convert__cache_delete_length">
    <td width="40%">
        <label for="cache_delete_length">
            <?= Loc::getMessage("D2F_COMPRESS_CACHE_DELETE_LENGTH") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            name="cache_delete_length"
            value="<?=Option::get($curModuleName, 'cache_delete_length', 1000)?>"
        />
    </td>
</tr>


<?php foreach ($arSites as $arSite) { ?>
    <?php
    $convert = new \Dev2fun\ImageCompress\Convert($arSite['ID']);
    ?>

    <tr class="heading">
        <td colspan="2" class="accordion_heading" data-site="<?=$arSite['ID']?>">
            <div>
                <h2><?= Loc::getMessage("D2F_COMPRESS_SITE_SETTINGS")?> "<?=\Bitrix\Main\Text\HtmlFilter::encode($arSite['NAME'])?> [<?=$arSite['ID']?>]"</h2>
                <div class="adm-detail-title-setting" title="<?= Loc::getMessage('D2F_COMPRESS_SITE_HEADING_TITLE')?>">
                    <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
                </div>
            </div>
        </td>
    </tr>

    <div class="accordion_content" data-site="<?=$arSite['ID']?>">

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
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][convert_enable]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "convert_enable", 'N', $arSite['ID']) === 'Y') {
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
                <select
                    id="convert_mode"
                    class="select__convert_mode"
                    name="options[<?=$arSite['ID']?>][convert_mode][]"
                    multiple
                >
                    <?php
                    $selectConvertMode = $convert->convertMode;
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
            <td></td>
            <td>
                <?php
                echo BeginNote();
                echo Loc::getMessage('D2F_IMAGECOMPRESS_MODE_CONVERT_TEXT');
                echo EndNote();
                ?>
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_CONVERT') ?>:</label>
            </td>
            <td width="60%">
                <select name="options[<?=$arSite['ID']?>][convert_algorithm]">
                    <?php
                    $selectAlgorithmConvert = Option::get($curModuleName, 'convert_algorithm', 'phpWebp', $arSite['ID']);
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
                <select name="options[<?=$arSite['ID']?>][convert_quality]">
                    <?php
                    $webpQuality = Option::get($curModuleName, 'convert_quality', 80, $arSite['ID']);
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
                <table
                    class="nopadding"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    width="100%"
                    id="d2f_page_excluded_webp"
                >
                    <tbody>
                    <?php
                    $excludedPages = \Dev2fun\ImageCompress\Convert::getSettingsExcludePage($arSite['ID']);
                    $serverUrl = \Dev2funImageCompress::getUrl('/');
                    foreach ($excludedPages as $key => $page) {
                        $key = \str_replace('n', '', $key);
                        ?>
                        <tr>
                            <td>
                                <label><?= $serverUrl ?></label>
                                <input
                                    name="options[<?=$arSite['ID']?>][EXCLUDE_PAGES][n<?= $key ?>]"
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
                                name="options[<?=$arSite['ID']?>][EXCLUDE_PAGES][n<?= count($excludedPages) ?>]"
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
                echo EndNote();
                ?>
            </td>
        </tr>

        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <label>
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_FILE_EXCLUDED"); ?>:
                </label>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <table
                    class="nopadding"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    width="100%"
                    id="d2f_file_excluded_webp"
                >
                    <tbody>
                    <?php
                    $excludedPages = \Dev2fun\ImageCompress\Convert::getSettingsExcludeFiles($arSite['ID']);
                    $serverUrl = \Dev2funImageCompress::getUrl('/');
                    foreach ($excludedPages as $key => $page) {
                        $key = \str_replace('n', '', $key);
                        ?>
                        <tr>
                            <td>
                                <label><?= $serverUrl ?></label>
                                <input
                                    name="options[<?=$arSite['ID']?>][EXCLUDE_FILES][n<?= $key ?>]"
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
                                name="options[<?=$arSite['ID']?>][EXCLUDE_FILES][n<?= count($excludedPages) ?>]"
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
                echo EndNote();
                ?>
            </td>
        </tr>

        <!--  Включение режима отдачи пути до оригинального файла  -->
<!--        <tr>-->
<!--            <td width="40%">-->
<!--                <label for="enable_section">-->
<!--                    --><?php //= Loc::getMessage("D2F_COMPRESS_ENABLE_ORIGINAL_PICTURES_MODE") ?><!--:-->
<!--                </label>-->
<!--            </td>-->
<!--            <td width="60%">-->
<!--                <input type="checkbox"-->
<!--                       name="options[--><?php //=$arSite['ID']?><!--][orig_pictures_mode]"-->
<!--                       value="Y"-->
<!--                    --><?php
//                    if (Option::get($curModuleName, "orig_pictures_mode", 'N', $arSite['ID']) === 'Y') {
//                        echo 'checked';
//                    }
//                    ?>
<!--                />-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td></td>-->
<!--            <td>-->
<!--                --><?php
//                echo BeginNote();
//                echo Loc::getMessage('D2F_COMPRESS_ORIGINAL_PICTURES_MODE_TEXT');
//                echo EndNote();
//                ?>
<!--            </td>-->
<!--        </tr>-->

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
                       name="options[<?=$arSite['ID']?>][path_to_cwebp]"
                       value="<?= Option::get($curModuleName, "path_to_cwebp", '/usr/bin', $arSite['ID']); ?>"
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
                <select name="options[<?=$arSite['ID']?>][cwebp_compress]">
                    <?php
                    $cwebpCompress = Option::get($curModuleName, "cwebp_compress", 4, $arSite['ID']);
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
                       name="options[<?=$arSite['ID']?>][cwebp_multithreading]"
                       value="Y"
                    <?php
                    if (Option::get($curModuleName, 'cwebp_multithreading', 'Y', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>

    </div>
<?php } ?>