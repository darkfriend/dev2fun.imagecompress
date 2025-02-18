<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.11.0
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Dev2fun\ImageCompress\Convert;

$curModuleName = "dev2fun.imagecompress";
Loader::includeModule($curModuleName);

IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/
$canRead = $USER->CanDoOperation('imagecompress_list_read');
$canWrite = $USER->CanDoOperation('imagecompress_list_write');
if (!$canRead && !$canWrite) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$EDITION_RIGHT = $APPLICATION->GetGroupRight($curModuleName);
if ($EDITION_RIGHT === "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

function GetDirectorySize($path, $type = 'MB') {

    switch ($type) {
        case 'MB': $divider = 1024**2; break;
        case 'GB': $divider = 1024**3; break;
        case 'TB': $divider = 1024**4; break;
        default:
            $type = 'KB';
            $divider = 1024; break;
    }

    $count = 0;
    $bytesTotal = 0;
    $path = realpath($path);
    if ($path && file_exists($path)) {
        /** @var RecursiveDirectoryIterator $object */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
            $bytesTotal += $object->getSize();
            if ($object->isFile()) {
                $count++;
            }
        }
    }

    return [
        'size' => round($bytesTotal / $divider, 2) . " {$type}",
        'countFiles' => $count,
    ];
}

function GetCountFiles($path)
{
    $count = 0;
    $path = realpath($path);
    if ($path && file_exists($path)) {
        /** @var RecursiveDirectoryIterator $object */
//        $s = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
//        $c = count($s->get);
//        var_dump($c);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
            if ($object->isFile()) {
                $count++;
            }
        }
    }

//    var_dump($count);

    return $count;
}

function GetFileNameByConvertFilename(string $dir, string $convertFilename)
{
//    var_dump($dir);
//    var_dump($convertFilename);
    $path = realpath($dir);
//    var_dump('$path='.$path);
    if ($path && file_exists($path)) {
        /** @var RecursiveDirectoryIterator $object */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
            $basename = $object->getBasename(".{$object->getExtension()}");
//            var_dump("\$basename=$basename");
//            var_dump($object->isFile());
            if ($object->isFile() && $basename === $convertFilename) {
                return $object->getPathname();
            }
        }
    }

    return null;
}

//function getNewPath()
//{
//    $moduleName = Dev2funImageCompress::MODULE_ID;
//    $uploadDir = Option::get('main', 'upload_dir', 'upload');
//    return "/{$uploadDir}/{$moduleName}";
//}

//$aTabs = [
//    [
//        "DIV" => "main",
//        "TAB" => Loc::getMessage("SEC_MAIN_TAB"),
//        "ICON" => "main_user_edit",
//        "TITLE" => Loc::getMessage("SEC_MAIN_TAB_TITLE"),
//    ],
//];

//$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

//$bVarsFromForm = false;
$APPLICATION->SetTitle(Loc::getMessage("DEV2FUN_IMAGECOMPRESS_CONVERT_TITLE"));

$paths = [
    'webp' => "{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/webp",
    'avif' => "{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/avif",
];

$recCompress = null;
if (
    check_bitrix_sessid()
    && (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        || !empty($_REQUEST["action"])
    )
//    && ($_REQUEST["action"] ?? '') === "converted_move"
) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $context = \Bitrix\Main\Context::getCurrent();
        $requestBody = $context->getRequest()->getJsonList()->toArray();
    } else {
        $requestBody['action'] = $_REQUEST['action'] ?? '';
    }

    $result = [
        'success' => false,
        'msg' => '',
        'body' => null,
    ];

    try {
        $moveDir = "{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/webp";

        switch ($requestBody["action"]) {
            case "init":
                $result['body'] = [
                    'moveDirs' => [
                        'webp' => "{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/webp",
                        'avif' => "{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/avif",
                    ],
//                    'currentDirSize' => $oldDirInfo['size'],
//                    'currentCountFiles' => $oldDirInfo['countFiles'],
                ];

            case "startConvert":
//                if ($requestBody["action"] === "startConvert") {
//                    var_dump($requestBody);
//                    die();
//                }
                $cntRows = \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::query()
                    ->whereLike('IMAGE_PATH', '/upload/resize_cache%')
                    ->queryCountTotal();
                $result['body']['cntPictures'] = $cntRows;
                $result['success'] = true;
                break;

            case "updateDataMoveDir":
                $sizes = [];
                foreach ($paths as $key => $path) {
                    if (!is_dir($path)) {
                        $sizes[$key] = 0;
                        continue;
                    }
                    $sizes[$key] = GetDirectorySize($path);
                }
                $result['body'] = $sizes;
//                $oldDirInfo = GetDirectorySize("{$_SERVER['DOCUMENT_ROOT']}/upload/resize_cache/webp");
//                $result['body'] = [
//                    'currentDirSize' => $oldDirInfo['size'],
//                    'currentCountFiles' => $oldDirInfo['countFiles'],
//                ];
                $result['success'] = true;
                break;

            case "convertMove":
//                var_dump($requestBody);
//                die();

//                sleep(10);
//                $result['body'] = [];
//                $result['success'] = true;
//                break;

                $rows = \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::query()
                    ->setSelect([
                        'ID',
                        'IMAGE_PATH',
                    ])
                    ->whereLike('IMAGE_PATH', '/upload/resize_cache%')
                    ->setLimit($requestBody["countPage"] ?? 500)
                    ->fetchAll();

//                var_dump($rows);
//                die();

                if (!$rows) {
                    $result['msg'] = Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_MSG_SUCCESSFUL");
                    $result['success'] = true;
                    break;
                }

                $errors = [];
                $pathNew = Convert::getPath() . '/';
                foreach ($rows as $row) {
                    $newImagePath = str_replace('/upload/resize_cache/', $pathNew, $row['IMAGE_PATH']);
                    if ($newImagePath !== $row['IMAGE_PATH']) {
                        \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::update(
                            $row['ID'],
                            [
                                'IMAGE_PATH' => $newImagePath,
                            ]
                        );
                    }

                    $absImagePath = $_SERVER['DOCUMENT_ROOT'] . $row['IMAGE_PATH'];
                    $absNewImagePath = $_SERVER['DOCUMENT_ROOT'] . $newImagePath;

                    $dirname = dirname($absNewImagePath);
                    if (
                        !is_dir($dirname)
                        && !mkdir($dirname, \BX_DIR_PERMISSIONS, true)
                    ) {
//                        throw new \Exception("Не смог создать папку {$dirname}");
                        throw new \Exception(
                            Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_ERROR_CREATE_DIR", ['#DIR#' => $dirname])
                        );
                    }

//                    var_dump($absImagePath);
//                    var_dump($absNewImagePath);
//                    die();

                    if (is_file($absNewImagePath)) {
                        unlink($absImagePath);
                    } else {
                        if (!rename($absImagePath, $absNewImagePath)) {
//                            $errors[] = "Не смог перенести файл {$absImagePath}";
                            $errors[] = Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_ERROR_MOVE_FILE", ['#FILE#' => $absImagePath]);
                        }
                    }
                }

                if ($errors) {
//                    $result['success'] = false;
                    throw new \Exception(implode("\n", $errors));
                }

                $result['body'] = [
                    'errorFiles' => $errors,
                ];
                $result['success'] = true;
                break;

            case 'convertMoveFiles':

                $requestBody["countPage"] = (int)($requestBody["countPage"] ?? 500);

//                sleep(10);
//                $result['body'] = [
//                    'cntMovedFiles' => $requestBody["countPage"],
//                    'errorFiles' => [],
//                ];
//                $result['success'] = true;
//                break;

//                var_dump($requestBody);
//                die();

                $errFiles = [];
                $cntMovedFiles = 0;
                foreach ($paths as $extFile => $valPath) {
                    $path = realpath($valPath);
                    if ($path && file_exists($path)) {
                        /** @var RecursiveDirectoryIterator $object */
                        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
//                            $bytesTotal += $object->getSize();
                            if ($object->isFile()) {
                                if ($cntMovedFiles >= $requestBody["countPage"]) {
                                    break 2;
                                }

                                $imgPath = $object->getPath();
                                $imgPath = str_replace($valPath, '', $imgPath);
                                $imgPath = str_replace('/upload', '', $imgPath);

                                $sourcePath = "{$_SERVER['DOCUMENT_ROOT']}/upload{$imgPath}";

//                                var_dump($object->getPathname());
//                                var_dump($object->getFileInfo());
//                                var_dump($object->getFilename());
//                                var_dump($object->getBasename(".{$object->getExtension()}"));
//                                var_dump($object->getFileInfo());
//                                var_dump($object->getExtension());
//                                var_dump(
//                                    GetFileNameByConvertFilename(
//                                        $sourcePath,
//                                        $object->getBasename(".{$object->getExtension()}")
//                                    )
//                                );
//                                die();
//                                $imgPath = $object->getPathname();
//                                $imgPath = str_replace($valPath, '', $imgPath);
//                                $imgPath = str_replace(['.webp', '.avif'], '', $imgPath);



//                                var_dump("\sourcePath: {$sourcePath}");
//                                var_dump("\getBasename: {$object->getBasename(".{$object->getExtension()}")}");

                                $sourceFile = GetFileNameByConvertFilename(
                                    $sourcePath,
                                    $object->getBasename(".{$object->getExtension()}")
                                );

//                                var_dump("\$sourceFile={$sourceFile}");

//                                if (!is_file("{$_SERVER['DOCUMENT_ROOT']}/upload{$imgPath}")) {
                                if (!is_file($sourceFile)) {
//                                    var_dump("No file: {$sourceFile}");
//                                    var_dump("remove file: {$object->getPathname()}");
                                    if (!unlink($object->getPathname())) {
                                        $errFiles[] = $object->getPathname();
                                    }
                                } else {

                                    $movePath = "{$_SERVER['DOCUMENT_ROOT']}/upload/dev2fun.imagecompress/{$extFile}{$imgPath}/{$object->getFilename()}";

                                    $dirname = dirname($movePath);
                                    if (
                                        !is_dir($dirname)
                                        && !mkdir($dirname, \BX_DIR_PERMISSIONS, true)
                                    ) {
//                                        throw new \Exception("Не смог создать папку {$dirname}");
                                        throw new \Exception(Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_ERROR_CREATE_DIR", ['#DIR#' => $dirname]));
                                    }

                                    $isRenamed = rename(
                                        $object->getPathname(),
                                        $movePath
                                    );
//                                    $isRenamed = true;
//                                    var_dump("rename file: {$object->getPathname()}");
//                                    var_dump("rename to {$_SERVER['DOCUMENT_ROOT']}/upload/dev2fun.imagecompress/{$extFile}{$imgPath}/{$object->getFilename()}");

                                    if (!$isRenamed) {
                                        $errFiles[] = $object->getPathname();
                                    }
                                }

                                $cntMovedFiles++;
                            }
                        }
                    }
                }

                $result['body'] = [
                    'cntMovedFiles' => $cntMovedFiles,
                    'errorFiles' => $errFiles,
                ];
                $result['success'] = true;
                break;

            case 'analysisPhysicalFiles':
                $cnt = 0;
                foreach ($paths as $key => $path) {
                    if (!is_dir($path)) {
                        continue;
                    }
                    $cnt += GetCountFiles($path) ?? 0;
                }

                $result['body'] = [
                    'cntFiles' => $cnt,
                ];
                $result['success'] = true;
                break;

            default:
                throw new \Exception('Method not implemented');
        }
    } catch (Exception $e) {
        $result['msg'] = $e->getMessage();
    }

    echo json_encode($result);
    return;
}

CJSCore::Init(['jquery']);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$cntRows = \Dev2fun\ImageCompress\ImageCompressImagesConvertedTable::query()
    ->whereLike('IMAGE_PATH', '/upload/resize_cache%')
    ->queryCountTotal();

$isPathExists = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        $isPathExists = true;
    }
}

if ($cntRows > 0 || $isPathExists) {
    $fileUpdateTime = filemtime($_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/dev2fun.imagecompress/vue/js/main.bundle.js');

    $messages = [
        'infoTitle' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_TITLE"),
        'infoUpdateBtn' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_UPDATE_BTN"),
        'infoText' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_TEXT"),
        'infoIn' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_IN"),
        'infoCountFiles' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_COUNT_FILES"),
        'infoDirSize' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_DIR_SIZE"),
        'infoDirCountFiles' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_DIR_COUNT_FILES"),
        'admMessageSuccessful' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_ADMMESSAGE_SUCCESSFUL"),
        'progressBarTitle' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_PROGRESSBAR_TITLE"),
        'progressBarText' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_PROGRESSBAR_TEXT"),
        'progressBarStopBtn' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_PROGRESSBAR_STOP_BTN"),
        'countFilesPerStep' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_COUNT_FILES_PER_STEP"),
        'moveFilesBtn' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_FILES_BTN"),
        'errCountFilesPerStep' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_ERR_COUNT_FILES_PER_STEP"),

        'infoTextSteps' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_TEXT_STEPS"),
        'infoTextStep1' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_TEXT_STEP1"),
        'infoTextStep2' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_INFO_TEXT_STEP2"),
        'headingStep1' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_HEADING_STEP1"),
        'headingStep2' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_HEADING_STEP2"),


        'headingFilesDb' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_HEADING_FILES_DB"),
        'headingFilesPhysical' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_HEADING_FILES_PHYSICAL"),
        'step2ProgressText' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_STEP2_PROGRESS_TEXT"),
        'step2ProgressTextReminder' => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_STEP2_PROGRESS_TEXT_REMINDER"),
    ];
    $messages = json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    echo <<<HTML
        <script src="/bitrix/js/dev2fun.imagecompress/vue/js/main.bundle.js?v1&v={$fileUpdateTime}" defer></script>
        <script>
            window.d2fLocalMessages = {$messages};
        </script>
        <div id="dev2fun_imagecompress_convert_move">
            <mover-files></mover-files>
        </div>
HTML;
} else {
    CAdminMessage::ShowMessage([
        "MESSAGE" => Loc::getMessage("D2F_IMAGECOMPRESS_CONVERT_MOVE_SUCCESSFUL_ALL"),
        "TYPE" => "OK",
    ]);
}
