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

<!--    <tr class="heading">-->
<!--        <td colspan="2">-->
<!--            <b>--><?php //= Loc::getMessage("D2F_COMPRESS_GLOBAL_SETTINGS") ?><!--</b>-->
<!--        </td>-->
<!--    </tr>-->

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
                name="common_options[enable_jpeg]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_jpeg", 'N') === 'Y') {
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
            <select name="common_options[opti_algorithm_jpeg]">
                <?php
                $selectAlgorithmJpeg = Option::get($curModuleName, "opti_algorithm_jpeg", '');
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
                name="common_options[path_to_jpegoptim]"
                value="<?= Option::get($curModuleName, "path_to_jpegoptim", '/usr/bin'); ?>"
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
            <select name="common_options[jpegoptim_compress]">
                <?php
                $jpgCompress = (int)Option::get($curModuleName, "jpegoptim_compress", '80');
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
                name="common_options[jpeg_progressive]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "jpeg_progressive", 'N') === 'Y') {
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
                name="common_options[enable_png]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_png", 'N') === 'Y') {
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
            <select name="common_options[opti_algorithm_png]">
                <?php
                $selectAlgorithmPng = Option::get($curModuleName, "opti_algorithm_png", '');
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
                name="common_options[path_to_optipng]"
                value="<?= Option::get($curModuleName, "path_to_optipng", '/usr/bin'); ?>"
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
            <select name="common_options[optipng_compress]">
                <?php
                $pngCompress = (int)Option::get($curModuleName, "optipng_compress", '3');
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
                name="common_options[enable_pdf]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_pdf", 'N') === 'Y') {
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
                name="common_options[path_to_ps2pdf]"
                value="<?= Option::get($curModuleName, "path_to_ps2pdf", '/usr/bin'); ?>"
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
            <select name="common_options[pdf_setting]">
                <?php
                $pdfSetting = Option::get($curModuleName, "pdf_setting", 'ebook');
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
    $optTypePath = __DIR__."/../include/options/{$optType}.php";
    if (!file_exists($optTypePath)) {
        continue;
    }
    echo "<!-- $optType -->";
    include $optTypePath;
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
    <tr>
        <td width="40%">
            <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_LABEL_MODULES_SELECT') ?> (beta):</label>
        </td>
        <td width="60%">
            <select name="common_options[modules][]" multiple disabled>
                <?php
                $supportModules = [
                    'fileman',
                    'iblock',
                    'advertising',
                    'sale',
                    'main',
                    'other',
                ];
                $selectModules = Option::get(
                    $curModuleName,
                    "opti_support_modules",
                    $supportModules
                );
                if (!is_array($selectModules) && !empty($selectModules)) {
                    $selectModules = json_decode($selectModules);
                }
                if (!is_array($selectModules)) {
                    $selectModules = [];
                }
                foreach ($supportModules as $v) { ?>
                    <option value="<?= $v ?>" <?= (in_array($v, $selectModules) ? 'selected' : '') ?>>
                        <?= $v === 'other'
                            ? Loc::getMessage('D2F_IMAGECOMPRESS_LABEL_MODULES_SELECT_MODULE_OTHER')
                            : Loc::getMessage('D2F_IMAGECOMPRESS_LABEL_MODULES_SELECT_MODULE', ['#MODULE#' => $v])
                        ?>
                    </option>
                <?php } ?>
            </select>
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
                name="common_options[enable_element]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_element", 'N') === 'Y') {
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
                name="common_options[enable_section]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_section", 'N') === 'Y') {
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
                name="common_options[enable_resize]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_resize", 'N') === 'Y') {
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
            </label>enable_resize
        </td>
        <td width="60%">
            <input
                type="checkbox"
                name="common_options[enable_save]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable_save", 'N') === 'Y') {
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
                name="common_options[resize_image_enable]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "resize_image_enable", 'N') === 'Y') {
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
                name="common_options[resize_image_width]"
                value="<?= Option::get($curModuleName, "resize_image_width", '') ?>"
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
                name="common_options[resize_image_height]"
                value="<?= Option::get($curModuleName, "resize_image_height", '') ?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_OPTIONS_RESIZE_IMAGE_ALGORITHM_SELECT') ?>:</label>
        </td>
        <td width="60%">
            <?php
            $selectResizeAlgorithm = Option::get($curModuleName, "resize_image_algorithm", '');
            foreach ($resizeAlgorithm as $k => $v) { ?>
                <label>
                    <input
                        type="radio"
                        name="common_options[resize_image_algorithm]"
                        value="<?= $k ?>" <?= ($selectResizeAlgorithm == $k) ? 'checked' : '' ?>
                    >
                    <?= $v ?>
                </label>
                <br>
            <?php } ?>
        </td>
    </tr>




<?php /* foreach ($arSites as $arSite) { ?>

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



    </div>
<?php } */ ?>