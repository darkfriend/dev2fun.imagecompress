<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.6.6
 */

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use \Dev2fun\ImageCompress\Check;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = "dev2fun.imagecompress";
Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule(\Dev2funImageCompress::MODULE_ID);

$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET_OPTI"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ],
    [
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_CONVERT"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_CONVERT"),
    ],
    [
        "DIV" => "donate",
        "TAB" => Loc::getMessage('SEC_DONATE_TAB'),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage('SEC_DONATE_TAB_TITLE'),
    ],
    //    array(
    //        "DIV" => "edit2",
    //        "TAB" => Loc::getMessage("MAIN_TAB_6"),
    //        "ICON" => "main_settings",
    //        "TITLE" => Loc::getMessage("MAIN_OPTION_REG")
    //    ),
    //    array(
    //        "DIV" => "edit3",
    //        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
    //        "ICON" => "main_settings",
    //        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    //    ),
    //    array("DIV" => "edit8", "TAB" => GetMessage("MAIN_TAB_8"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_EVENT_LOG")),
    //    array("DIV" => "edit5", "TAB" => GetMessage("MAIN_TAB_5"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_UPD")),
    //    array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
];

//$tabControl = new CAdminTabControl("tabControl", array(
//    array(
//        "DIV" => "edit1",
//        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
//        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
//    ),
//));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {

    if ($request->getPost('test_module')) {
        $text = [];
        $error = false;
        $algorithmJpeg = Option::get($curModuleName, 'opti_algorithm_jpeg');
        $algorithmPng = Option::get($curModuleName, 'opti_algorithm_png');
        foreach (Check::$optiClasses as $algKey=>$algItem) {
            if(!Check::isOptim($algKey)) {
                $text[] = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => $algKey]);
            }
        }
        if (!$text) {
            $text = Loc::getMessage("D2F_COMPRESS_OPTIONS_TESTED");
        } else {
            $error = true;
            $text = \implode(
                PHP_EOL,
                \array_merge(
                    [Loc::getMessage("D2F_COMPRESS_OPTIONS_NO_TESTED")],
                    $text
                )
            );
        }
        CAdminMessage::showMessage([
            "MESSAGE" => $text,
            "TYPE" => (!$error ? 'OK' : 'ERROR'),
        ]);
    } else {
        try {
            $success = false;
            $updCheckbox = [];
            $updString = [];

            // TODO: do refactor!
            // save jpeg
            $enableJpeg = $request->getPost('enable_jpeg');
            $algorithmJpeg = $request->getPost('opti_algorithm_jpeg');
            $pthJpeg = $request->getPost('path_to_jpegoptim');
            if ($pthJpeg) {
                $pthJpeg = rtrim($pthJpeg, '/');
            }
            if($enableJpeg==='Y') {
                if(!$pthJpeg) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'jpeg']));
                }
                if(!$algorithmJpeg) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', ['#MODULE#' => 'jpeg']));
                }
                if (!Check::isJPEGOptim($algorithmJpeg)) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
                }
            }
            $updCheckbox['enable_jpeg'] = $enableJpeg;
            $updString['opti_algorithm_jpeg'] = $algorithmJpeg;
            $updString['path_to_jpegoptim'] = $pthJpeg;

            // TODO: do refactor!
            // save png
            $enablePng = $request->getPost('enable_png');
            $algorithmPng = $request->getPost('opti_algorithm_png');
            $pthPng = $request->getPost('path_to_optipng');
            if ($pthPng) {
                $pthPng = rtrim($pthPng, '/');
            }
            if($enablePng==='Y') {
                if(!$pthPng) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'png']));
                }
                if(!$algorithmPng) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', ['#MODULE#' => 'png']));
                }
                if (!Check::isPNGOptim($algorithmPng)) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
                }
            }
            $updCheckbox['enable_png'] = $enablePng;
            $updString['opti_algorithm_png'] = $algorithmPng;
            $updString['path_to_optipng'] = $pthPng;


            // TODO: do refactor!
            // save pdf
            $enablePdf = $request->getPost('enable_pdf');
            $ps2pdf = $request->getPost('path_to_ps2pdf');
            if ($ps2pdf) {
                $ps2pdf = \rtrim($ps2pdf, '/');
            }
            if($enablePdf==='Y') {
                if(!$ps2pdf) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'pdf']));
                }
                if (!Check::isOptim('ps2pdf')) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
                }
            }
            $updCheckbox['enable_pdf'] = $enablePdf;
//            $updString['opti_algorithm_png'] = $algorithmPng;
            $updString['path_to_ps2pdf'] = $ps2pdf;

            $pdfSetting = $request->getPost('pdf_setting');
            $updString['pdf_setting'] = !empty($pdfSetting) ? $pdfSetting : 'ebook';


            $saveTypes = [
                //                'webp',
                'gif',
                'svg',
            ];
            foreach ($saveTypes as $saveType) {
                // save type
                $enable = $request->getPost('enable_'.$saveType, 'N');
                $algorithm = $request->getPost('opti_algorithm_'.$saveType);
                $pth = $request->getPost('path_to_'.$saveType, '/usr/bin');
                if ($pth) {
                    $pth = \rtrim($pth, '/');
                }
                if($enable==='Y') {
                    if(!$pth) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => $saveType]));
                    }
                    if(!$algorithm) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', ['#MODULE#' => $saveType]));
                    }
                    if (!Check::isOptim($algorithm)) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => $saveType]));
                    }
                }
                $updCheckbox['enable_'.$saveType] = $enable;
                $updString['opti_algorithm_'.$saveType] = $algorithm;
                $updString['path_to_'.$saveType] = $pth;

                // advanced settings
                $advanceSettings = \Dev2fun\ImageCompress\Compress::getAlgInstance($saveType)
                    ->getOptionsSettings($request->getPost($saveType, []));
                if($advanceSettings && !empty($advanceSettings['checkbox'])) {
                    $updCheckbox = \array_merge($updCheckbox, $advanceSettings['checkbox']);
                }
                if($advanceSettings && !empty($advanceSettings['string'])) {
                    $updString = \array_merge($updString, $advanceSettings['string']);
                }
            }

            if (!empty($_REQUEST["EXCLUDE_PAGES"])) {
                \Dev2fun\ImageCompress\Convert::saveSettingsExcludePage($_REQUEST["EXCLUDE_PAGES"]);
            }

            // set convert options
            $updCheckbox['convert_enable'] = $request->getPost('convert_enable', 'N') === 'Y';
            $updCheckbox['cwebp_multithreading'] = $request->getPost('cwebp_multithreading', 'N') === 'Y';

            $updString['convert_mode'] = $request->getPost('convert_mode');
            if(!$updString['convert_mode']) {
                $updString['convert_mode'] = [];
            }

//            $updString['convert_attributes'] = $request->getPost('convertAttr');
//            if(\is_array($updString['convert_attributes'])) {
//                $updString['convert_attributes'] = \array_filter($updString['convert_attributes'], function($item) {
//                    return !empty($item);
//                });
//            } else {
//                $updString['convert_attributes'] = [];
//            }

            $updString['convert_algorithm'] = $request->getPost('convert_algorithm', 'phpWebp');
            $updString['webp_quality'] = $request->getPost('webp_quality', '80');
            $updString['path_to_cwebp'] = $request->getPost('path_to_cwebp', '/usr/bin');
            if ($updString['path_to_cwebp']) {
                $updString['path_to_cwebp'] = \rtrim($updString['path_to_cwebp'], '/');
            }
            if($updString['convert_algorithm']==='cwebp') {
                if(!$updString['path_to_cwebp']) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'cwebp']));
                }
                if (!Check::isOptim('cwebp')) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'cwebp']));
                }
            }
            $updString['cwebp_compress'] = $request->getPost('cwebp_compress', '4');
            $updString['cache_time'] = $request->getPost('cache_time');
            if(!$updString['cache_time']) $updString['cache_time'] = 3600;


            if($updCheckbox) {
                foreach ($updCheckbox as $kOption => $vOption) {
                    Option::set($curModuleName, $kOption, ($vOption ? 'Y' : 'N'));
                }
            }
            if($updString) {
                foreach ($updString as $kOption => $vOption) {
                    if(\is_array($vOption)) {
                        $vOption = \serialize($vOption);
                    }
                    Option::set($curModuleName, $kOption, $vOption);
                }
            }


            $cntStep = $request->getPost('cnt_step');
            if (!$cntStep) $cntStep = 30;
            Option::set($curModuleName, 'cnt_step', $cntStep);

            $chmod = $request->getPost('change_chmod');
            if (!isset($chmod)) {
                $chmod = 777;
            } else {
                $chmod = (int)$chmod;
            }
            Option::set($curModuleName, 'change_chmod', $chmod);

            $enableElement = $request->getPost('enable_element');
            Option::set($curModuleName, 'enable_element', ($enableElement ? 'Y' : 'N'));

            $enableSection = $request->getPost('enable_section');
            Option::set($curModuleName, 'enable_section', ($enableSection ? 'Y' : 'N'));

            $enableResize = $request->getPost('enable_resize');
            Option::set($curModuleName, 'enable_resize', ($enableResize ? 'Y' : 'N'));

            $enableSave = $request->getPost('enable_save');
            Option::set($curModuleName, 'enable_save', ($enableSave ? 'Y' : 'N'));

            Option::set($curModuleName, 'jpegoptim_compress', $request->getPost('jpegoptim_compress'));
            Option::set($curModuleName, 'optipng_compress', $request->getPost('optipng_compress'));

            $jpegCompress = $request->getPost('jpeg_progressive');
            Option::set($curModuleName, 'jpeg_progressive', ($jpegCompress ? 'Y' : 'N'));

            $resizeImageEnable = $request->getPost('resize_image_enable');
            Option::set($curModuleName, 'resize_image_enable', ($resizeImageEnable ? 'Y' : 'N'));
            if ($resizeImageEnable) {
                $resizeImageWidth = $request->getPost('resize_image_width');
                if (!$resizeImageWidth) $resizeImageWidth = 1280;
                Option::set($curModuleName, 'resize_image_width', $resizeImageWidth);

                $resizeImageHeight = $request->getPost('resize_image_height');
                if (!$resizeImageHeight) $resizeImageHeight = 99999;
                Option::set($curModuleName, 'resize_image_height', $resizeImageHeight);

                $resizeImageAlgorithm = $request->getPost('resize_image_algorithm');
                if (!$resizeImageAlgorithm) $resizeImageAlgorithm = 0;
                Option::set($curModuleName, 'resize_image_algorithm', $resizeImageAlgorithm);
            } else {
                Option::set($curModuleName, 'resize_image_width', '');
                Option::set($curModuleName, 'resize_image_height', '');
                Option::set($curModuleName, 'resize_image_algorithm', 0);
            }

            $msg = Loc::getMessage("D2F_COMPRESS_REFERENCES_OPTIONS_SAVED");
            $success = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        CAdminMessage::showMessage([
            "MESSAGE" => $msg,
            "TYPE" => ($success ? 'OK' : 'ERROR'),
        ]);
    }
}
$tabControl->begin();
?>

<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.cards.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.responsive.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.containers.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.tables.min.css">

<form
    method="post"
    action="<?= \sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), \urlencode($mid), LANGUAGE_ID) ?>"
>
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    $optiAlgorithmJpeg = [
        'jpegoptim' => 'Jpegoptim',
    ];
    $optiAlgorithmPng = [
        'optipng' => 'Optipng',
    ];
    $optiAlgorithmList = [
        'jpg' => [
            'jpegoptim',
        ],
        'png' => [
            'optipng',
        ],
        'pdf' => [
            'ps2pdf',
        ],
        'webp' => [
            'cwebp',
        ],
        'gif' => [
            'gifsicle',
        ],
        'svg' => [
            'svgo',
        ],
    ];
    $resizeAlgorithm = [
        \BX_RESIZE_IMAGE_PROPORTIONAL => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_PROPORTIONAL'),
        \BX_RESIZE_IMAGE_EXACT => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_EXACT'),
        \BX_RESIZE_IMAGE_PROPORTIONAL_ALT => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_PROPORTIONAL_ALT'),
    ];
    ?>
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
            <input type="checkbox"
                   name="enable_jpeg"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_jpeg") === 'Y') {
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
            <select name="opti_algorithm_jpeg">
                <?php
                $selectAlgorithmJpeg = Option::get($curModuleName, "opti_algorithm_jpeg");
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
            <input type="text"
                   size="50"
                   name="path_to_jpegoptim"
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
            <select name="jpegoptim_compress">
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
            <input type="checkbox"
                   name="jpeg_progressive"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "jpeg_progressive") === 'Y') {
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
            <input type="checkbox"
                   name="enable_png"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_png") === 'Y') {
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
            <select name="opti_algorithm_png">
                <?php
                $selectAlgorithmPng = Option::get($curModuleName, "opti_algorithm_png");
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
            <input type="text"
                   size="50"
                   name="path_to_optipng"
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
            <select name="optipng_compress">
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
            <input type="checkbox"
                   name="enable_pdf"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_pdf") === 'Y') {
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
            <input type="text"
                   size="50"
                   name="path_to_ps2pdf"
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
            <select name="pdf_setting">
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
            <input type="checkbox"
                   name="enable_element"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_element") === 'Y') {
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
            <input type="checkbox"
                   name="enable_section"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_section") === 'Y') {
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
            <input type="checkbox"
                   name="enable_resize"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_resize") === 'Y') {
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
            <input type="checkbox"
                   name="enable_save"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "enable_save") === 'Y') {
                    echo 'checked';
                }
                ?>
            />
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="cnt_step">
                <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CNT_STEP") ?>:
            </label>
        </td>
        <td width="60%">
            <input type="text"
                   name="cnt_step"
                   value="<?= Option::get($curModuleName, "cnt_step", 30) ?>"
            />
        </td>
    </tr>

    <tr>
        <td width="40%">
            <label for="cnt_step">
                <?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CHMOD") ?>:
            </label>
        </td>
        <td width="60%">
            <input type="text"
                   name="change_chmod"
                   value="<?= Option::get($curModuleName, 'change_chmod', '777') ?>"
            />
        </td>
    </tr>


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
            <input type="checkbox"
                   name="resize_image_enable"
                   value="Y"
                <?php
                if (Option::get($curModuleName, "resize_image_enable") == 'Y') {
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
            <input type="text"
                   name="resize_image_width"
                   value="<?= Option::get($curModuleName, "resize_image_width") ?>"
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
            <input type="text"
                   name="resize_image_height"
                   value="<?= Option::get($curModuleName, "resize_image_height") ?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label><?= Loc::getMessage('D2F_IMAGECOMPRESS_OPTIONS_RESIZE_IMAGE_ALGORITHM_SELECT') ?>:</label>
        </td>
        <td width="60%">
            <?php
            $selectResizeAlgorithm = Option::get($curModuleName, "resize_image_algorithm");
            foreach ($resizeAlgorithm as $k => $v) { ?>
                <label>
                    <input type="radio" name="resize_image_algorithm"
                           value="<?= $k ?>" <?= ($selectResizeAlgorithm == $k) ? 'checked' : '' ?>>
                    <?= $v ?>
                </label>
                <br>
            <?php } ?>
        </td>
    </tr>

    <?php include __DIR__.'/tabs/convert.php'?>
    <?php include __DIR__.'/tabs/donate.php'?>

    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?= Loc::getMessage("MAIN_SAVE") ?>"
           title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
    />
    <input type="submit"
           name="test_module"
           value="<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_TEST_BTN") ?>"
    />
    <?php /* ?>
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
    />
    <? */ ?>
    <?php $tabControl->end(); ?>
</form>