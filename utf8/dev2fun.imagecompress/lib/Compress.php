<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.6.8
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Compress
{
    private $jpegoptim = false;
    private $pngoptim = false;
    private $MODULE_ID = 'dev2fun.imagecompress';
    private $png = false;
    private $tableName = 'b_d2f_imagecompress_files';
    public $LAST_ERROR;

    private $optiClassJpeg = '';
    private $optiClassPng = '';

    private
        $algorithmJpeg = '',
        $algorithmPng = '';

    private
        $jpegOptimPath = '',
        $pngOptimPath = '';

    private
        $enableElement = false,
        $enableSection = false,
        $enableResize = false,
        $enableSave = false,
        $enableImageResize = false,
        $jpegProgress = false;

    private
        $jpegOptimCompress,
        $pngOptimCompress;

    private $algorithmClass;

    public static $supportContentType = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'image/svg',
        'image/gif',
    ];

    private static $optiClasses = [
        'jpegoptim' => '\Dev2fun\ImageCompress\Jpegoptim',
        'optipng' => '\Dev2fun\ImageCompress\Optipng',
        'ps2pdf' => '\Dev2fun\ImageCompress\Ps2Pdf',
        'svg' => '\Dev2fun\ImageCompress\Svg',
        'gif' => '\Dev2fun\ImageCompress\Gif',
    ];

    private static $instance;
    /** @var bool state */
    protected static $enable = true;

    private function __construct()
    {
        $this->algorithmJpeg = Option::get($this->MODULE_ID, 'opti_algorithm_jpeg', 'jpegoptim');
        $this->algorithmPng = Option::get($this->MODULE_ID, 'opti_algorithm_png', 'optipng');

        $this->pngOptimPath = Option::get($this->MODULE_ID, 'path_to_optipng');
        $this->jpegOptimPath = Option::get($this->MODULE_ID, 'path_to_jpegoptim');

        $this->enableElement = (Option::get($this->MODULE_ID, 'enable_element') === 'Y');
        $this->enableSection = (Option::get($this->MODULE_ID, 'enable_section') === 'Y');
        $this->enableResize = (Option::get($this->MODULE_ID, 'enable_resize') === 'Y');
        $this->enableSave = (Option::get($this->MODULE_ID, 'enable_save') === 'Y');

        $this->jpegOptimCompress = Option::get($this->MODULE_ID, 'jpegoptim_compress', 80);
        $this->pngOptimCompress = Option::get($this->MODULE_ID, 'optipng_compress', 3);

        $this->jpegProgress = (Option::get($this->MODULE_ID, 'jpeg_progressive') === 'Y');
        $this->enableImageResize = (Option::get($this->MODULE_ID, 'resize_image_enable') === 'Y');
    }

    /**
     * @static
     * @return Compress
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /**
     * Get algorithm class
     * @param string $algorithm
     * @return null|Jpegoptim|Optipng|Ps2Pdf|Webp|Gif|Svg
     */
    public static function getAlgInstance($algorithm)
    {
        $obj = null;
        switch ($algorithm) {
            case 'jpegoptim':
                $obj = \Dev2fun\ImageCompress\Jpegoptim::getInstance();
                break;
            case 'optipng':
                $obj = \Dev2fun\ImageCompress\Optipng::getInstance();
                break;
            case 'ps2pdf':
                $obj = \Dev2fun\ImageCompress\Ps2Pdf::getInstance();
                break;
            case 'webp':
            case 'cwebp':
                $obj = \Dev2fun\ImageCompress\Webp::getInstance();
                break;
            case 'gif':
            case 'gifsicle':
                $obj = \Dev2fun\ImageCompress\Gif::getInstance();
                break;
            case 'svg':
            case 'svgo':
                $obj = \Dev2fun\ImageCompress\Svg::getInstance();
                break;
        }
        return $obj;
//		return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
    }

    public function isPNGOptim()
    {
        if (!$this->pngoptim) {
            $this->pngoptim = Check::isPNGOptim($this->algorithmPng);
            if (!$this->pngoptim) $this->LAST_ERROR = Check::$lastError;
//            exec($this->pngOptimPath.'/optipng -v',$s);
//            if($s) $this->pngoptim = true;
        }
        return $this->pngoptim;
    }

    public function isJPEGOptim()
    {
        if (!$this->jpegoptim) {
            $this->jpegoptim = Check::isJPEGOptim($this->algorithmJpeg);
            if (!$this->jpegoptim) $this->LAST_ERROR = Check::$lastError;
//            exec($this->jpegOptimPath.'/jpegoptim --version',$s);
//            if($s) $this->jpegoptim = true;
        }
        return $this->jpegoptim;
    }

    public function compressJPG($strFilePath)
    {
        if(!static::$enable) return null;
        $res = false;
        if (!$this->isJPEGOptim()) {
            if (empty($this->LAST_ERROR)) {
                $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => 'jpegoptim']);
            }
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $algInstance = static::getAlgInstance($this->algorithmJpeg);
            $res = $algInstance->compressJPG(
                $strFilePath,
                $this->jpegOptimCompress,
                [
                    'progressiveJpeg' => $this->jpegProgress,
                    'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
                ]
            );
        }
        return $res;
    }

    public function compressPNG($strFilePath)
    {
        if(!static::$enable) return null;
        $res = false;
        if (!$this->isPNGOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => 'optipng']);
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $algInstance = static::getAlgInstance($this->algorithmPng);
            $res = $algInstance->compressPNG(
                $strFilePath,
                $this->pngOptimCompress,
                [
                    'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
                ]
            );
        }
        return $res;
    }

    public function compressPdf($strFilePath)
    {
        if(!static::$enable) return null;
        $res = false;
        $algInstance = static::getAlgInstance('ps2pdf');
        if (!$algInstance->isPdfOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => 'ps2pdf']);
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $res = $algInstance->compress(
                $strFilePath,
                [
                    'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
                ]
            );
        }
        return $res;
    }

    /**
     * Запускает процесс
     * @param string $strFilePath
     * @param string $alg
     * @param array $options
     * @return bool|null
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function process($strFilePath, $alg=null, $options=[])
    {
        if(!static::$enable) return null;
        $res = false;
        if(!$alg) {
            return $res;
        }
        $algInstance = static::getAlgInstance($alg);
        if (!$algInstance->isOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => $alg]);
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $res = $algInstance->compress(
                $strFilePath,
                \array_merge(
                    [
                        'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
                    ],
                    $options
                )
            );
        }
        return $res;
    }

    /**
     * Compress image by fileID
     * @param integer $intFileID
     * @return bool|null
     */
    public function compressImageByID($intFileID)
    {
        global $DB;
        $res = false;
        if(!static::$enable) return null;
        if (!$intFileID) return null;

        $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforeResizeImage", [$intFileID]);
        $event->send();

        $arFile = \CFile::GetByID($intFileID)->GetNext();
        if (
            !\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)
            || !Check::isActiveByMimeType($arFile["CONTENT_TYPE"])
        ) {
            return null;
        }

        $strFilePath = $_SERVER["DOCUMENT_ROOT"] . \CFile::GetPath($intFileID);

        if (\file_exists($strFilePath)) {
            $oldSize = $arFile["FILE_SIZE"]; // filesize($strFilePath);
            if ($this->enableImageResize) {
                $this->resize($intFileID, $strFilePath);
            }
            switch ($arFile["CONTENT_TYPE"]) {
                case 'image/jpeg' :
                    $isCompress = $this->compressJPG($strFilePath);
                    break;
                case 'image/png' :
                    $isCompress = $this->compressPNG($strFilePath);
                    break;
                case 'application/pdf' :
                    $isCompress = $this->compressPdf($strFilePath);
                    break;
                case 'image/svg' :
                    $isCompress = $this->process(
                        $strFilePath,
                        Option::get($this->MODULE_ID, 'opti_algorithm_svg', '')
                    );
                    break;
                case 'image/gif' :
                    $isCompress = $this->process(
                        $strFilePath,
                        Option::get($this->MODULE_ID, 'opti_algorithm_gif', '')
                    );
                    break;
                default :
                    $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONTENT_TYPE', [
                        '#TYPE#' => $arFile["CONTENT_TYPE"]
                    ]);
                    return null;
            }

            if ($isCompress) {
                \clearstatcache(true, $strFilePath);
                $newSize = filesize($strFilePath);
                if ($newSize != $oldSize) {
                    //					$DB->Query("UPDATE b_file SET FILE_SIZE='" . $DB->ForSql($newSize, 255) . "' WHERE ID=" . intval($intFileID));
                    $this->saveSizeBitrix($intFileID, $newSize);
                }
                $arFields = [
                    'FILE_ID' => $intFileID,
                    'SIZE_BEFORE' => $oldSize,
                    'SIZE_AFTER' => $newSize,
                ];
                $rs = ImageCompressTable::getById($intFileID);
                if ($rs->getSelectedRowsCount() <= 0) {
                    $el = new ImageCompressTable();
                    $res = $el->add($arFields);
                } else {
                    $res = ImageCompressTable::update($intFileID, $arFields);
                }
            }
        } else {
            $res = $this->addCompressTable($intFileID, [
                'FILE_ID' => $intFileID,
                'SIZE_BEFORE' => 0,
                'SIZE_AFTER' => 0,
            ]);
        }
        return $res;
    }

    /**
     * Resize image file
     * @param integer $fileId
     * @param string $strFilePath
     * @return bool
     */
    public function resize($fileId, $strFilePath)
    {
        if(!static::$enable) return false;
        if (!$strFilePath) return false;
        if (!$this->enableImageResize) return false;

        $width = Option::get($this->MODULE_ID, 'resize_image_width');
        $height = Option::get($this->MODULE_ID, 'resize_image_height');
        $algorithm = Option::get($this->MODULE_ID, 'resize_image_algorithm');
        if (!$algorithm) $algorithm = BX_RESIZE_IMAGE_PROPORTIONAL;

        $destinationFile = $_SERVER['DOCUMENT_ROOT'] . "/upload/{$this->MODULE_ID}/" . basename($strFilePath);
        $res = \CFile::ResizeImageFile(
            $strFilePath,
            $destinationFile,
            [
                'width' => $width,
                'height' => $height,
            ],
            $algorithm
        );
        if ($res) {
            \chmod($destinationFile, 0777);
            \copy($destinationFile, $strFilePath);
            $this->saveWidthHeight($fileId, $strFilePath);
            \unlink($destinationFile);
        }
        return $res;
    }

    public function saveWidthHeight($fileId, $filepath)
    {
        global $DB;
        \clearstatcache(true, $filepath);
        $arImageSize = \CFile::GetImageSize($filepath);
        return $DB->Query("UPDATE b_file SET HEIGHT='" . \round((float)$arImageSize[1]) . "', WIDTH='" . \round((float)$arImageSize[0]) . "' WHERE ID=" . (int)$fileId);
    }

    /**
     * Save filesize in table b_file
     * @param integer $fileId
     * @param integer $newSize
     * @return mixed
     */
    public function saveSizeBitrix($fileId, $newSize)
    {
        global $DB;
        return $DB->Query("UPDATE b_file SET FILE_SIZE='" . $DB->ForSql($newSize, 255) . "' WHERE ID=" . (int)$fileId);
    }

    public function addCompressTable($intFileID, $arFields)
    {
        $rs = ImageCompressTable::getById($intFileID);

        if ($rs->getSelectedRowsCount() <= 0) {
            $el = new ImageCompressTable();
            $res = $el->add($arFields);
        } else {
            $res = ImageCompressTable::update($intFileID, $arFields);
        }
        return $res;
    }

    /**
     * Сжатие картинок на событии в разделах
     * @param array $arFields
     */
    public static function CompressImageOnSectionEvent(&$arFields)
    {
        if(!static::$enable) return;
        $instance = self::getInstance();
        if ($instance->enableSection && !empty($arFields['PICTURE'])) {
            $rsSection = \CIBlockSection::GetByID($arFields["ID"]);
            $arSection = $rsSection->GetNext();
            $instance->compressImageByID($arSection['PICTURE']);
        }
    }

    /**
     * Сжатие картинок на событии в элементах
     * @param array $arFields
     */
    public static function CompressImageOnElementEvent(&$arFields)
    {
        if(!static::$enable) return;
        $instance = self::getInstance();
        if (!$instance->enableElement) {
            return;
        }
//        var_dump($instance->enableElement); die();
        if ((int)$arFields["PREVIEW_PICTURE_ID"] > 0) {
            $instance->compressImageByID($arFields["PREVIEW_PICTURE_ID"]);
        }

        if ((int)$arFields["DETAIL_PICTURE_ID"] > 0) {
            $instance->compressImageByID($arFields["DETAIL_PICTURE_ID"]);
        }

        $arEl = false;

        if ($arFields["PROPERTY_VALUES"]) {
            foreach ($arFields["PROPERTY_VALUES"] as $key => $values) {
                foreach ($values as $v) {
                    if (\is_array($v)) {

//                        $contentTypeList = [
//                            'image/jpeg',
//                            'image/png',
//                            'application/pdf',
//                        ];
                        if (
                            !isset($v['VALUE']['type'])
                            || (isset($v['VALUE']['type']) && !\in_array($v['VALUE']['type'], static::$supportContentType))
                            || !Check::isActiveByMimeType($v['VALUE']['type'])
                        ) {
                            continue;
                        }

                        if (!$arEl) {
                            $rsEl = \CIBlockElement::GetByID($arFields["ID"]);
                            if ($obEl = $rsEl->GetNextElement()) {
                                $arEl = $obEl->GetFields();
                                $arEl["PROPERTIES"] = $obEl->GetProperties();
                            }
                        }

                        foreach ($arEl["PROPERTIES"] as $arProp) {
                            if ($arProp["ID"] == $key) {
                                if ($arProp["MULTIPLE"] !== 'N') {
                                    foreach ($arProp["VALUE"] as $intFileID) {
                                        $instance->compressImageByID($intFileID);
                                    }
                                } else {
                                    $instance->compressImageByID($arProp["VALUE"]);
                                }
                            }
                        }

                    }
                }
            }
        }
    }

    /**
     * Сжатие картинок на событии в сохранения в таблице
     */
    public static function CompressImageOnFileEvent(&$arFile, $strFileName, $strSavePath, $bForceMD5, $bSkipExt)
    {
        if(!static::$enable) return;
        $instance = self::getInstance();
        if (!$instance->enableSave) return;

//        $contentTypeList = [
//            'image/jpeg',
//            'image/png',
//            'application/pdf',
//        ];
        if (!\in_array($arFile["type"], static::$supportContentType)) {
            return;
        }
        if(!Check::isActiveByMimeType($arFile["type"])) {
            return null;
        }

        switch ($arFile["type"]) {
            case 'image/jpeg' :
                $isCompress = $instance->compressJPG($arFile["tmp_name"]);
                break;
            case 'image/png' :
                $isCompress = $instance->compressPNG($arFile["tmp_name"]);
                break;
            case 'application/pdf' :
                $isCompress = $instance->compressPdf($arFile["tmp_name"]);
                break;
            case 'image/svg' :
                $isCompress = $instance->process(
                    $arFile["tmp_name"],
                    Option::get($instance->MODULE_ID, 'opti_algorithm_svg', '')
                );
                break;
            case 'image/gif' :
                $isCompress = $instance->process(
                    $arFile["tmp_name"],
                    Option::get($instance->MODULE_ID, 'opti_algorithm_gif', '')
                );
                break;
        }
        if ($isCompress) {
            $arFile["size"] = \filesize($arFile["tmp_name"]);
        }

    }

    /**
     *  delete picture
     */
    public static function CompressImageOnFileDeleteEvent($arFile)
    {
        ImageCompressTable::delete($arFile['ID']);
    }

    public static function CompressImageOnResizeEvent(
        $arFile,
        $arParams,
        &$callbackData,
        &$cacheImageFile,
        &$cacheImageFileTmp,
        &$arImageSize
    )
    {
        if(!static::$enable) return;
        $instance = self::getInstance();
        if (!$instance->enableResize) return;

//        $contentTypeList = [
//            'image/jpeg',
//            'image/png',
//            'application/pdf',
//        ];
        if (
            !\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)
            || !Check::isActiveByMimeType($arFile["CONTENT_TYPE"])
        ) {
            return null;
        }
        switch ($arFile["CONTENT_TYPE"]) {
            case 'image/jpeg' :
                $instance->compressJPG($cacheImageFileTmp);
                break;
            case 'image/png' :
                $instance->compressPNG($cacheImageFileTmp);
                break;
            case 'application/pdf' :
                $instance->compressPdf($cacheImageFileTmp);
                break;
            case 'image/svg' :
                $instance->process(
                    $cacheImageFileTmp,
                    Option::get($instance->MODULE_ID, 'opti_algorithm_svg', '')
                );
                break;
            case 'image/gif' :
                $instance->process(
                    $cacheImageFileTmp,
                    Option::get($instance->MODULE_ID, 'opti_algorithm_gif', '')
                );
                break;
        }
    }

    public function queryBuilder($arOrder = [], $arFilter = [])
    {
        global $DB;
        $arSqlSearch = [];
        $arSqlOrder = [];
        $strSqlSearch = $strSqlOrder = "";

        if (\is_array($arFilter)) {
            foreach ($arFilter as $key => $val) {
                $key = \strtoupper($key);

                $strOperation = '';
                if (\substr($key, 0, 1) === "@") {
                    $key = \substr($key, 1);
                    $strOperation = "IN";
                    $arIn = \is_array($val) ? $val : \explode(',', $val);
                    $val = '';
                    foreach ($arIn as $v) {
                        $val .= ($val <> '' ? ',' : '') . "'" . $DB->ForSql(\trim($v)) . "'";
                    }
                } elseif (\substr($val, 0, 1) === ">") {
                    $val = \substr($val, 1);
                    $strOperation = ">";
                    $arIn = \is_array($val) ? $val : \explode(',', $val);
                    $val = '';
                    foreach ($arIn as $v) {
                        $val .= ($val <> '' ? ',' : '') . "'" . $DB->ForSql(\trim($v)) . "'";
                    }
                } elseif (\substr($val, 0, 1) === "<") {
                    $val = \substr($val, 1);
                    $strOperation = "<";
                    $arIn = \is_array($val) ? $val : \explode(',', $val);
                    $val = '';
                    foreach ($arIn as $v) {
                        $val .= ($val <> '' ? ',' : '') . "'" . $DB->ForSql(\trim($v)) . "'";
                    }
                } else {
                    $val = $DB->ForSql($val);
                }

                if ($val === '') {
                    continue;
                }

                switch ($key) {
                    case "MODULE_ID":
                    case "ID":
                    case "EXTERNAL_ID":
                    case "SUBDIR":
                    case "FILE_NAME":
                    case "FILE_SIZE":
                    case "ORIGINAL_NAME":
                    case "CONTENT_TYPE":
                        if ($strOperation === "IN")
                            $arSqlSearch[] = "f." . $key . " IN (" . $val . ")";
                        elseif ($strOperation === ">")
                            $arSqlSearch[] = "f." . $key . " > " . $val . "";
                        elseif ($strOperation === "<")
                            $arSqlSearch[] = "f." . $key . " < " . $val . "";
                        else
                            $arSqlSearch[] = "f." . $key . " = '" . $val . "'";
                        break;
                    case "COMRESSED":
                        if ($val === "Y")
                            $arSqlSearch[] = "tf.FILE_ID > 0";
                        else
                            $arSqlSearch[] = "tf.FILE_ID is NULL";
                        break;
                    case "COMPRESSED":
                        $arSqlSearch[] = "tf.COMPRESSED = $val";
                        break;
                }
            }
        }

        if (!empty($arSqlSearch)) {
            $strSqlSearch = " WHERE (" . \implode(") AND (", $arSqlSearch) . ")";
        }

        if (\is_array($arOrder)) {
            static $aCols = [
                "ID" => 1,
                "TIMESTAMP_X" => 1,
                "MODULE_ID" => 1,
                "HEIGHT" => 1,
                "WIDTH" => 1,
                "FILE_SIZE" => 1,
                "CONTENT_TYPE" => 1,
                "SUBDIR" => 1,
                "FILE_NAME" => 1,
                "ORIGINAL_NAME" => 1,
                "EXTERNAL_ID" => 1,
            ];
            foreach ($arOrder as $by => $ord) {
                $by = \strtoupper($by);
                if (\array_key_exists($by, $aCols))
                    $arSqlOrder[] = "f." . $by . " " . (\strtoupper($ord) === "DESC" ? "DESC" : "ASC");
            }
        }
        if (empty($arSqlOrder))
            $arSqlOrder[] = "f.ID ASC";
        $strSqlOrder = " ORDER BY " . \implode(", ", $arSqlOrder);

        $strSql =
            "SELECT f.*, " . $DB->DateToCharFunction("f.TIMESTAMP_X") . " as TIMESTAMP_X, tf.* " .
            "FROM b_file f " .
            "LEFT JOIN {$this->tableName} as tf ON f.ID = tf.FILE_ID" .
            $strSqlSearch .
            $strSqlOrder;

        return $strSql;
    }

    public function getFileList($arOrder = [], $arFilter = [], $limit = 100, $offset = 0)
    {
        global $DB;
        $strSql = $this->queryBuilder($arOrder, $arFilter);
        //        if($limit) {
        //            $strSql .= ' LIMIT '.$limit;
        //        }
        //
        //        if($offset) {
        //            $strSql .= ' OFFSET '.$offset;
        //        }
        return $DB->Query($strSql, false, "FILE: " . __FILE__ . "<br> LINE: " . __LINE__);
    }

    public function getNiceFileSize($fileSize, $digits = 2)
    {
        $sizes = ["TB", "GB", "MB", "KB", "B"];
        $total = \count($sizes);
        while ($total-- && $fileSize > 1024) {
            $fileSize /= 1024;
        }
        return \round($fileSize, $digits) . " " . $sizes[$total];
    }

    public function getChmod($num)
    {
        if (!$num) return 0777;
        switch ((int)$num) {
            case 644:
                $num = 0644;
                break;
            case 660:
                $num = 0660;
                break;
            case 664:
                $num = 0664;
                break;
            case 666:
                $num = 0666;
                break;
            case 700:
                $num = 0700;
                break;
            case 744:
                $num = 0744;
                break;
            case 755:
                $num = 0755;
                break;
            case 775:
                $num = 0775;
                break;
            case 777:
                $num = 0777;
                break;
            default:
                $num = 0777;
        }
        return $num;
    }

    /**
     * Set state module
     * @param bool $enable
     */
    public static function setEnable($enable)
    {
        static::$enable = $enable;
    }

    /**
     * Get current state module
     * @return bool
     */
    public static function getEnable()
    {
        return static::$enable;
    }
}