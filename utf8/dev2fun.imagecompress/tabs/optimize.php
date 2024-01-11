<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

/** @var array $arSites */
/** @var string $curModuleName */
/** @var array $optiAlgorithmJpeg */
/** @var array $optiAlgorithmPng */
/** @var array $resizeAlgorithm */
?>

<tr class="heading">
    <td colspan="2">
        <b><?= Loc::getMessage("D2F_COMPRESS_GLOBAL_SETTINGS") ?></b>
    </td>
</tr>

<tr>
    <td width="40%">
        <label for="cnt_step">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CNT_STEP") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            name="cnt_step"
            value="<?= Option::get($curModuleName, "cnt_step", 30) ?>"
        />
    </td>
</tr>
<tr>
    <td width="40%">
        <label for="change_chmod">
            <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CHMOD") ?>:
        </label>
    </td>
    <td width="60%">
        <input
            type="text"
            name="change_chmod"
            value="<?= Option::get($curModuleName, 'change_chmod', '777') ?>"
        />
    </td>
</tr>

<?php foreach ($arSites as $arSite) { ?>

    <tr class="heading">
        <td colspan="2" class="accordion_heading" data-site="<?=$arSite['ID']?>">
            <div>
                <h2>
                    <?= Loc::getMessage('D2F_COMPRESS_SITE_SETTINGS')?> "<?=\Bitrix\Main\Text\HtmlFilter::encode($arSite['NAME'])?> [<?=$arSite['ID']?>]"
                </h2>
                <div class="adm-detail-title-setting" title="<?= Loc::getMessage('D2F_COMPRESS_SITE_HEADING_TITLE')?>">
                    <span class="adm-detail-title-setting-btn adm-detail-title-expand"></span>
                </div>
            </div>
        </td>
    </tr>

    <div class="accordion_content" data-site="<?=$arSite['ID']?>">

        <!-- JPEG-->
        <tr class="heading">
            <td colspan="2">
                <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', ['#MODULE#' => 'JPEG']) ?></b>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="enable_jpeg">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_OPTIMIZE_TO", ['#MODULE#' => 'jpeg']) ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_jpeg]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_jpeg", 'N', $arSite['ID']) === 'Y') {
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
                <select name="options[<?=$arSite['ID']?>][opti_algorithm_jpeg]">
                    <?php
                    $selectAlgorithmJpeg = Option::get($curModuleName, "opti_algorithm_jpeg", '',  $arSite['ID']);
                    foreach ($optiAlgorithmJpeg as $k => $v) { ?>
                        <option value="<?= $k ?>" <?= ($k === $selectAlgorithmJpeg ? 'selected' : '') ?>><?= $v ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="path_to_jpegoptim">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_JPEGOPTI") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    size="50"
                    name="options[<?=$arSite['ID']?>][path_to_jpegoptim]"
                    value="<?= Option::get($curModuleName, "path_to_jpegoptim", '/usr/bin', $arSite['ID']); ?>"
                /> /jpegoptim
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="jpegoptim_compress">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_JPEG_COMPRESS") ?>:
                </label>
            </td>
            <td width="60%">
                <select name="options[<?=$arSite['ID']?>][jpegoptim_compress]">
                    <?php
                    $jpgCompress = (int)Option::get($curModuleName, "jpegoptim_compress", '80', $arSite['ID']);
                    for ($i = 0; $i <= 100; $i += 5) { ?>
                        <option value="<?= $i ?>" <?= ($i === $jpgCompress ? 'selected' : '') ?>><?= $i ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <!--    JPEG SETTINGS-->
        <tr>
            <td width="40%">
                <label for="enable_element">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_JPEG_PROGRESSIVE") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][jpeg_progressive]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "jpeg_progressive", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>


        <!--    PNG-->
        <tr class="heading">
            <td colspan="2">
                <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', ['#MODULE#' => 'PNG']) ?></b>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="enable_png">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_OPTIMIZE_TO", ['#MODULE#' => 'png']) ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_png]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_png", 'N', $arSite['ID']) === 'Y') {
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
                <select name="options[<?=$arSite['ID']?>][opti_algorithm_png]">
                    <?php
                    $selectAlgorithmPng = Option::get($curModuleName, "opti_algorithm_png", '', $arSite['ID']);
                    foreach ($optiAlgorithmPng as $k => $v) { ?>
                        <option value="<?= $k ?>" <?= ($k === $selectAlgorithmPng ? 'selected' : '') ?>><?= $v ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="path_to_optipng">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_PNGOPTI") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    size="50"
                    name="options[<?=$arSite['ID']?>][path_to_optipng]"
                    value="<?= Option::get($curModuleName, "path_to_optipng", '/usr/bin', $arSite['ID']); ?>"
                /> /optipng
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="optipng_compress">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PNG_COMPRESS") ?>:
                </label>
            </td>
            <td width="60%">
                <select name="options[<?=$arSite['ID']?>][optipng_compress]">
                    <?php
                    $pngCompress = (int)Option::get($curModuleName, "optipng_compress", '3', $arSite['ID']);
                    for ($i = 1; $i <= 7; $i++) { ?>
                        <option value="<?= $i ?>" <?= ($i === $pngCompress ? 'selected' : '') ?>><?= $i ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>


        <!-- PDF -->
        <tr class="heading">
            <td colspan="2">
                <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', ['#MODULE#' => 'PDF']) ?></b>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="enable_pdf">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_OPTIMIZE_TO", ['#MODULE#' => 'pdf']) ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_pdf]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_pdf", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="path_to_ps2pdf">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_TO", ['#MODULE#' => 'gs']) ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    size="50"
                    name="options[<?=$arSite['ID']?>][path_to_ps2pdf]"
                    value="<?= Option::get($curModuleName, "path_to_ps2pdf", '/usr/bin', $arSite['ID']); ?>"
                /> /gs
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="pdf_setting">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PDF_SETTING_HEADING") ?>:
                </label>
            </td>
            <td width="60%">
                <select name="options[<?=$arSite['ID']?>][pdf_setting]">
                    <?php
                    $pdfSetting = Option::get($curModuleName, "pdf_setting", 'ebook', $arSite['ID']);
                    $pdfTypeSettings = [
                        'screen' => 'screen (72 dpi)',
                        'ebook' => 'ebook (150 dpi)',
                        'prepress' => 'prepress (300 dpi)',
                        'printer' => 'printer (300 dpi)',
                        'default' => 'default',
                    ];
                    foreach ($pdfTypeSettings as $key => $val) { ?>
                        <option value="<?= $key ?>" <?= ($key === $pdfSetting ? 'selected' : '') ?>>
                            <?= $val ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <!-- /PDF -->

        <?php
        foreach (Dev2funImageCompress::$supportFormats as $optType) {
            if(!file_exists(__DIR__."/include/options/{$optType}.php")) {
                continue;
            }
            echo "<!-- $optType -->";
            include __DIR__."/include/options/{$optType}.php";
            echo "<!-- /$optType -->";
        }
        ?>


        <tr class="heading">
            <td colspan="2">
                <b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_BASE_SETTINGS') ?></b>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="enable_element">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_ELEMENT") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_element]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_element", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label for="enable_section">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_SECTION") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_section]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_section", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label for="enable_resize">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_RESIZE") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_resize]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_resize", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label for="enable_save">
                    <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_SAVE") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][enable_save]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "enable_save", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>

        <!--  Инсключения оптимизаций по Инфоблокам  -->
        <!--    <tr>-->
        <!--        <td width="40%">-->
        <!--            <label for="enable_section">-->
        <!--                --><?php //= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_SECTION") ?><!--:-->
        <!--            </label>-->
        <!--        </td>-->
        <!--        <td width="60%">-->
        <!--            <input type="checkbox"-->
        <!--                   name="enable_section"-->
        <!--                   value="Y"-->
        <!--                --><?php
        //                if (Option::get($curModuleName, "enable_section") === 'Y') {
        //                    echo 'checked';
        //                }
        //                ?>
        <!--            />-->
        <!--        </td>-->
        <!--    </tr>-->


        <tr class="heading">
            <td colspan="2">
                <b><?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_HEADING") ?></b>
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="resize_image_enable">
                    <?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_ENABLE") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="checkbox"
                    name="options[<?=$arSite['ID']?>][resize_image_enable]"
                    value="Y"
                    <?php
                    if (Option::get($curModuleName, "resize_image_enable", 'N', $arSite['ID']) === 'Y') {
                        echo 'checked';
                    }
                    ?>
                />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="resize_image_width">
                    <?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_WIDTH") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    name="options[<?=$arSite['ID']?>][resize_image_width]"
                    value="<?= Option::get($curModuleName, "resize_image_width", '', $arSite['ID']) ?>"
                />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label for="resize_image_height">
                    <?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_HEIGHT") ?>:
                </label>
            </td>
            <td width="60%">
                <input
                    type="text"
                    name="options[<?=$arSite['ID']?>][resize_image_height]"
                    value="<?= Option::get($curModuleName, "resize_image_height", '', $arSite['ID']) ?>"
                />
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_OPTIONS_RESIZE_IMAGE_ALGORITHM_SELECT') ?>:</label>
            </td>
            <td width="60%">
                <?php
                $selectResizeAlgorithm = Option::get($curModuleName, "resize_image_algorithm", '', $arSite['ID']);
                foreach ($resizeAlgorithm as $k => $v) { ?>
                    <label>
                        <input
                            type="radio"
                            name="options[<?=$arSite['ID']?>][resize_image_algorithm]"
                            value="<?= $k ?>" <?= ($selectResizeAlgorithm == $k) ? 'checked' : '' ?>
                        >
                        <?= $v ?>
                    </label>
                    <br>
                <?php } ?>
            </td>
        </tr>

    </div>
<?php } ?>