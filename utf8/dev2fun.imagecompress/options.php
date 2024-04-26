<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.5
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

$arSites = Dev2funImageCompress::getSites();
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {

    if ($request->getPost('test_module')) {
        $text = [];
        $error = false;
        $arSite = current($arSites);

        $disableFunctions = ini_get('disable_functions');
        if ($disableFunctions && in_array('exec', explode(',', $disableFunctions))) {
            $text[] = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_EXEC');
        }

        foreach (Check::$optiClasses as $algKey => $algItem) {
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
    } else if (isset($_POST['action']) && $_POST['action'] === 'cache-deleted-agent') {
        $cacheDeletedAgentActive = ($_POST['active'] ?? 'N') === 'Y';
        if ($cacheDeletedAgentActive) {
            $res = \Dev2fun\ImageCompress\Cache::activateAgent();
        } else {
            $res = \Dev2fun\ImageCompress\Cache::deactivateAgent();
        }
        $APPLICATION->RestartBuffer();
        echo json_encode([
            'success' => $res,
        ]);
        die();
    } else {
        try {
            $success = false;
            $updCheckbox = [];
            $updString = [];

//            $typesOption = [
//                'enable_jpeg' => 'checkbox',
//                'opti_algorithm_jpeg' => 'string',
//                'path_to_jpegoptim' => 'string',
//
//                'enable_png' => 'checkbox',
//                'opti_algorithm_png' => 'string',
//                'path_to_optipng' => 'string',
//
//                'enable_pdf' => 'checkbox',
//                'path_to_ps2pdf' => 'string',
//                'pdf_setting' => 'string',
//            ];
//            $options = $request->getPost('options');
//            foreach ($options as $optionKey => $optionValue) {
//                switch ($optionKey) {
//                    case '': break;
//                }
//            }



            // TODO: do refactor!
            // save jpeg
            $enableJpeg = $_POST['common_options']['enable_jpeg'] ?? 'N';
            //            $enableJpeg =  $request->getPost('enable_jpeg');
            $algorithmJpeg = $_POST['common_options']['opti_algorithm_jpeg'] ?? '';
            //            $algorithmJpeg = $request->getPost('opti_algorithm_jpeg');
            $pthJpeg = $_POST['common_options']['path_to_jpegoptim'] ?? '';
            //            $pthJpeg = $request->getPost('path_to_jpegoptim');
            if ($pthJpeg) {
                $pthJpeg = rtrim($pthJpeg, '/');
            }
            if($enableJpeg === 'Y') {
                if(!$pthJpeg) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'jpeg']));
                }
                if(!$algorithmJpeg) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', ['#MODULE#' => 'jpeg']));
                }
                if (!Check::isOptim($algorithmJpeg, $pthJpeg)) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
                }
            }
            $updCheckbox['enable_jpeg'] = $enableJpeg === 'Y';
            $updString['opti_algorithm_jpeg'] = $algorithmJpeg;
            $updString['path_to_jpegoptim'] = $pthJpeg;

            // TODO: do refactor!
            // save png
            //            $enablePng = $request->getPost('enable_png');
            //            $algorithmPng = $request->getPost('opti_algorithm_png');
            //            $pthPng = $request->getPost('path_to_optipng');

            $enablePng = $_POST['common_options']['enable_png'] ?? 'N';
            $algorithmPng = $_POST['common_options']['opti_algorithm_png'] ?? '';
            $pthPng = $_POST['common_options']['path_to_optipng'] ?? '';
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
                if (!Check::isOptim($algorithmPng, $pthPng)) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'jpegoptim']));
                }
            }
            $updCheckbox['enable_png'] = $enablePng === 'Y';
            $updString['opti_algorithm_png'] = $algorithmPng;
            $updString['path_to_optipng'] = $pthPng;


            // TODO: do refactor!
            // save pdf
            //            $enablePdf = $request->getPost('enable_pdf');
            //            $ps2pdf = $request->getPost('path_to_ps2pdf');

            $enablePdf = $_POST['common_options']['enable_pdf'] ?? 'N';
            $ps2pdf = $_POST['common_options']['path_to_ps2pdf'] ?? '';
            if ($ps2pdf) {
                $ps2pdf = \rtrim($ps2pdf, '/');
            }
            if($enablePdf==='Y') {
                if(!$ps2pdf) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'pdf']));
                }
                if (!Check::isOptim('ps2pdf', $ps2pdf)) {
                    throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'ps2pdf']));
                }
            }
            $updCheckbox['enable_pdf'] = $enablePdf === 'Y';
            //            $updString['opti_algorithm_png'] = $algorithmPng;
            $updString['path_to_ps2pdf'] = $ps2pdf;

            //            $pdfSetting = $request->getPost('pdf_setting');
            $pdfSetting = $_POST['common_options'][$arSite['ID']]['pdf_setting'] ?? '';
            $updString['pdf_setting'] = !empty($pdfSetting) ? $pdfSetting : 'ebook';


            $saveTypes = [
                //                'webp',
                'gif',
                'svg',
            ];
            foreach ($saveTypes as $saveType) {
                // save type
                //                $enable = $request->getPost('enable_'.$saveType, 'N');
                //                $algorithm = $request->getPost('opti_algorithm_'.$saveType);
                //                $pth = $request->getPost('path_to_'.$saveType, '/usr/bin');

                $enable = $_POST['common_options']["enable_{$saveType}"] ?? 'N';
                $algorithm = $_POST['common_options']["opti_algorithm_{$saveType}"] ?? '';
                $pth = $_POST['common_options']["path_to_{$saveType}"] ?? '/usr/bin';
                //                $algorithm = $request->getPost('opti_algorithm_'.$saveType);
                //                $pth = $request->getPost('path_to_'.$saveType, '/usr/bin');
                if ($pth) {
                    $pth = \rtrim($pth, '/');
                }
                if($enable === 'Y') {
                    if(!$pth) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => $saveType]));
                    }
                    if(!$algorithm) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', ['#MODULE#' => $saveType]));
                    }
                    if ($saveType === 'svg') {
                        $pathNodejs = $_POST['common_options']["path_to_node"] ?? '/usr/bin';
                        if (!$pathNodejs) {
                            throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'nodejs']));
                        }
                        $pathNodejs = \rtrim($pathNodejs, '/');
                        if (!\Dev2fun\ImageCompress\Svg::getInstance()->isOptim($pth, $pathNodejs)) {
                            throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => "{$saveType}|nodejs"]));
                        }
                        $updString['path_to_node'] = $pathNodejs;
                    } else if (!Check::isOptim($algorithm, $pth)) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => $saveType]));
                    }
                }
                $updCheckbox['enable_'.$saveType] = $enable === 'Y';
                $updString['opti_algorithm_'.$saveType] = $algorithm;
                $updString['path_to_'.$saveType] = $pth;

                // advanced settings
                $advanceSettings = \Dev2fun\ImageCompress\Compress::getAlgInstance($saveType)
                    ->getOptionsSettings($_POST['common_options'][$saveType] ?? []);
                //                    ->getOptionsSettings($request->getPost($saveType, []));
                if($advanceSettings && !empty($advanceSettings['checkbox'])) {
                    foreach ($advanceSettings['checkbox'] as $k => $val) {
                        $updCheckbox[$k] = is_bool($val) ? $val : $val === 'Y';
                    }
                    //                        $updCheckbox = array_merge($updCheckbox, $advanceSettings['checkbox']);
                }
                if($advanceSettings && !empty($advanceSettings['string'])) {
                    foreach ($advanceSettings['string'] as $k => $val) {
                        $updString[$k] = $val;
                    }
                    //                        $updString = array_merge($updString, $advanceSettings['string']);
                }
            }


            if($updCheckbox) {
                foreach ($updCheckbox as $kOption => $vOption) {
                    Option::set(
                        $curModuleName,
                        $kOption,
                        $vOption ? 'Y' : 'N'
                    );
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



            //            $enableElement = $request->getPost('enable_element');
            $enableElement = $_POST['common_options']['enable_element'] ?? '';
            Option::set($curModuleName, 'enable_element', ($enableElement ? 'Y' : 'N'));

            //            $enableSection = $request->getPost('enable_section');
            $enableSection = $_POST['common_options']['enable_section'] ?? '';
            Option::set($curModuleName, 'enable_section', ($enableSection ? 'Y' : 'N'));

            //            $enableResize = $request->getPost('enable_resize');
            $enableResize = $_POST['common_options']['enable_resize'] ?? '';
            Option::set($curModuleName, 'enable_resize', ($enableResize ? 'Y' : 'N'));

            //            $enableSave = $request->getPost('enable_save');
            $enableSave = $_POST['common_options']['enable_save'] ?? '';
            Option::set($curModuleName, 'enable_save', ($enableSave ? 'Y' : 'N'));

            //            Option::set($curModuleName, 'jpegoptim_compress', $request->getPost('jpegoptim_compress'));
            //            Option::set($curModuleName, 'optipng_compress', $request->getPost('optipng_compress'));
            Option::set($curModuleName, 'jpegoptim_compress', $_POST['common_options']['jpegoptim_compress'] ?? '');
            Option::set($curModuleName, 'optipng_compress', $_POST['common_options']['optipng_compress'] ?? '');

            //            $jpegCompress = $request->getPost('jpeg_progressive');
            $jpegCompress = $_POST['common_options']['jpeg_progressive'] ?? '';
            Option::set($curModuleName, 'jpeg_progressive', ($jpegCompress ? 'Y' : 'N'));

            //            $resizeImageEnable = $request->getPost('resize_image_enable');
            $resizeImageEnable = $_POST['common_options']['resize_image_enable'] ?? '';
            Option::set($curModuleName, 'resize_image_enable', ($resizeImageEnable ? 'Y' : 'N'));
            if ($resizeImageEnable) {
                //                $resizeImageWidth = $request->getPost('resize_image_width');
                $resizeImageWidth = $_POST['common_options']['resize_image_width'] ?? '';
                if (!$resizeImageWidth) {
                    $resizeImageWidth = 1280;
                }
                Option::set($curModuleName, 'resize_image_width', $resizeImageWidth);

                //                $resizeImageHeight = $request->getPost('resize_image_height');
                $resizeImageHeight = $_POST['common_options']['resize_image_height'] ?? '';
                if (!$resizeImageHeight) {
                    $resizeImageHeight = 99999;
                }
                Option::set($curModuleName, 'resize_image_height', $resizeImageHeight);

                //                $resizeImageAlgorithm = $request->getPost('resize_image_algorithm');
                $resizeImageAlgorithm = $_POST['common_options']['resize_image_algorithm'] ?? '';
                if (!$resizeImageAlgorithm) {
                    $resizeImageAlgorithm = 0;
                }
                Option::set($curModuleName, 'resize_image_algorithm', $resizeImageAlgorithm);
            } else {
                Option::set($curModuleName, 'resize_image_width', '');
                Option::set($curModuleName, 'resize_image_height', '');
                Option::set($curModuleName, 'resize_image_algorithm', 0);
            }




            $updCheckbox = [];
            $updString = [];
            foreach ($arSites as $arSite) {

                if (!empty($_POST['options'][$arSite['ID']]["EXCLUDE_PAGES"])) {
                    \Dev2fun\ImageCompress\Convert::saveSettingsExcludePage(
                        $_POST['options'][$arSite['ID']]["EXCLUDE_PAGES"] ?? [],
                        $arSite['ID']
                    );
                }

                if (!empty($_POST['options'][$arSite['ID']]["EXCLUDE_FILES"])) {
                    \Dev2fun\ImageCompress\Convert::saveSettingsExcludeFile(
                        $_POST['options'][$arSite['ID']]["EXCLUDE_FILES"] ?? [],
                        $arSite['ID']
                    );
                }

                // set convert options
                //            $updCheckbox['convert_enable'] = $request->getPost('convert_enable', 'N') === 'Y';
                //            $updCheckbox['cwebp_multithreading'] = $request->getPost('cwebp_multithreading', 'N') === 'Y';

                $updCheckbox['convert_enable'] = ($_POST['options'][$arSite['ID']]['convert_enable'] ?? 'N') === 'Y';
                $updCheckbox['cwebp_multithreading'] = ($_POST['options'][$arSite['ID']]['cwebp_multithreading'] ?? 'N') === 'Y';

                //            $updString['convert_mode'] = $request->getPost('convert_mode');
                $updString['convert_mode'] = $_POST['options'][$arSite['ID']]['convert_mode'] ?? '';
//                var_dump($updString['convert_mode']); die();
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

                //            $updString['convert_algorithm'] = $request->getPost('convert_algorithm', 'phpWebp');
                //            $updString['convert_quality'] = $request->getPost('convert_quality', '80');
                //            $updString['path_to_cwebp'] = $request->getPost('path_to_cwebp', '/usr/bin');

                $updString['convert_algorithm'] = $_POST['options'][$arSite['ID']]['convert_algorithm'] ?? 'phpWebp';
                $updString['convert_quality'] = $_POST['options'][$arSite['ID']]['convert_quality'] ?? '80';
                $updString['path_to_cwebp'] = $_POST['options'][$arSite['ID']]['path_to_cwebp'] ?? '/usr/bin';

                if ($updString['path_to_cwebp']) {
                    $updString['path_to_cwebp'] = \rtrim($updString['path_to_cwebp'], '/');
                }
                if($updString['convert_algorithm'] === 'cwebp') {
                    if(!$updString['path_to_cwebp']) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NO_PATH_TO', ['#MODULE#' => 'cwebp']));
                    }
                    if (!Check::isOptim('cwebp', $updString['path_to_cwebp'])) {
                        throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', ['#MODULE#' => 'cwebp']));
                    }
                }
                //            $updString['cwebp_compress'] = $request->getPost('cwebp_compress', '4');
                $updString['cwebp_compress'] = $_POST['options'][$arSite['ID']]['cwebp_compress'] ?? '4';
                //            $updString['cache_time'] = $request->getPost('cache_time');
                $cacheTime = $_POST['options']['cache_time'] ?? '4';
                if(!$cacheTime) {
                    $cacheTime = 3600;
                }
                Option::set($curModuleName, 'cache_time', $cacheTime);


                if($updCheckbox) {
                    foreach ($updCheckbox as $kOption => $vOption) {
                        Option::set(
                            $curModuleName,
                            $kOption,
                            $vOption ? 'Y' : 'N',
                            $arSite['ID']
                        );
                    }
                }
                if($updString) {
                    foreach ($updString as $kOption => $vOption) {
                        if(\is_array($vOption)) {
                            $vOption = \serialize($vOption);
                        }
                        Option::set($curModuleName, $kOption, $vOption, $arSite['ID']);
                    }
                }

                Option::set(
                    $curModuleName,
                    'orig_pictures_mode',
                    //                $request->getPost('orig_pictures_mode') ? 'Y' : 'N'
                    ($_POST['options'][$arSite['ID']]['orig_pictures_mode'] ?? '') ? 'Y' : 'N',
                    $arSite['ID']
                );


                //            $cntStep = $request->getPost('cnt_step');
                $cntStep = $_POST['cnt_step'] ?? 30;
                if (!$cntStep) {
                    $cntStep = 30;
                }
                Option::set($curModuleName, 'cnt_step', $cntStep);

                //            $chmod = $request->getPost('change_chmod');
                $chmod = $_POST['change_chmod'] ?? 777;
                if (!isset($chmod)) {
                    $chmod = 777;
                } else {
                    $chmod = (int)$chmod;
                }
                Option::set($curModuleName, 'change_chmod', $chmod);

                // convert common settings
                Option::set(
                    $curModuleName,
                    'convert_per_page',
                    $_POST['convert_per_page'] ?? 200
                );
                Option::set(
                    $curModuleName,
                    'convert_cache_time_find_images',
                    $_POST['convert_cache_time_find_images'] ?? (3600*24)
                );
                Option::set(
                    $curModuleName,
                    'convert_cache_time_get_images',
                    $_POST['convert_cache_time_get_images'] ?? 3600
                );
                Option::set(
                    $curModuleName,
                    'convert_cache_include_user_groups',
                    $_POST['convert_cache_include_user_groups'] ?? 'N'
                );
            }

            Option::set($curModuleName, 'cache_delete_length', $_POST['cache_delete_length'] ?? 1000);

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

<script type="text/javascript">
    <?=file_get_contents(__DIR__.'/install/js/script.js');?>
</script>
<style>
    .accordion_heading {
        position: relative;
        cursor: pointer;
    }
    .accordion_heading .adm-detail-title-setting {
        bottom: 0;
        top: 50%;
        margin-top: -23px;
    }
</style>

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

    <?php include __DIR__.'/tabs/optimize.php'?>
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
    <?php */ ?>
    <?php $tabControl->end(); ?>
</form>