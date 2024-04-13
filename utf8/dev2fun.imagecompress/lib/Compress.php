<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.5
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
    private $tableName = 'b_d2f_imagecompress_files';

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

    public $enableJpeg = false;
    public $enablePng = false;
    public $enablePdf = false;
    public $enableGif = false;
    public $enableSvg = false;

    /** @var string[] */
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

    /** @var string[] */
    public static $supportContentType = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'image/svg',
        'image/gif',
    ];

    /** @var string */
    public $LAST_ERROR;

    /** @var bool */
    protected static $hasDisk;

    public function __construct()
    {
//        if (!$siteId) {
//            $siteId = \Dev2funImageCompress::getSiteId();
//        }
//        $this->siteId = $siteId;

        $this->algorithmJpeg = Option::get($this->MODULE_ID, 'opti_algorithm_jpeg', 'jpegoptim');
        $this->algorithmPng = Option::get($this->MODULE_ID, 'opti_algorithm_png', 'optipng');

        $this->pngOptimPath = Option::get($this->MODULE_ID, 'path_to_optipng', '');
        $this->jpegOptimPath = Option::get($this->MODULE_ID, 'path_to_jpegoptim', '');

        $this->enableJpeg = Option::get($this->MODULE_ID, 'enable_jpeg', 'N') === 'Y';
        $this->enablePng = Option::get($this->MODULE_ID, 'enable_png', 'N') === 'Y';
        $this->enablePdf = Option::get($this->MODULE_ID, 'enable_pdf', 'N') === 'Y';
        $this->enableGif = Option::get($this->MODULE_ID, 'enable_gif', 'N') === 'Y';
        $this->enableSvg = Option::get($this->MODULE_ID, 'enable_svg', 'N') === 'Y';

        $this->enableElement = (Option::get($this->MODULE_ID, 'enable_element', 'N') === 'Y');
        $this->enableSection = (Option::get($this->MODULE_ID, 'enable_section', 'N') === 'Y');
        $this->enableResize = (Option::get($this->MODULE_ID, 'enable_resize', 'N') === 'Y');
        $this->enableSave = (Option::get($this->MODULE_ID, 'enable_save', 'N') === 'Y');

        $this->jpegOptimCompress = Option::get($this->MODULE_ID, 'jpegoptim_compress', 80);
        $this->pngOptimCompress = Option::get($this->MODULE_ID, 'optipng_compress', 3);

        $this->jpegProgress = (Option::get($this->MODULE_ID, 'jpeg_progressive', 'N') === 'Y');
        $this->enableImageResize = (Option::get($this->MODULE_ID, 'resize_image_enable', 'N') === 'Y');
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
    public static function getAlgInstance(string $algorithm)
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

    public function compressJPG(string $strFilePath)
    {
        if(!static::$enable || !$this->enableJpeg) {
            return null;
        }
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

    public function compressPNG(string $strFilePath)
    {
        if(!static::$enable || !$this->enablePng) {
            return null;
        }
        $res = false;
        if (!$this->isPNGOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => 'optipng']);
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $res = static::getAlgInstance($this->algorithmPng)->compressPNG(
                $strFilePath,
                $this->pngOptimCompress,
                [
                    'changeChmod' => $this->getChmod(
                        Option::get($this->MODULE_ID, 'change_chmod', 777)
                    ),
                ]
            );
        }
        return $res;
    }

    public function compressPdf(string $strFilePath)
    {
        if(!static::$enable || !$this->enablePdf) {
            return null;
        }
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
                    'changeChmod' => $this->getChmod(
                        Option::get($this->MODULE_ID, 'change_chmod', 777)
                    ),
                ]
            );
        }
        return $res;
    }

    /**
     * Запускает процесс
     * @param string $strFilePath
     * @param string|null $alg
     * @param array $options
     * @return bool|null
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function process(string $strFilePath, ?string $alg=null, array $options=[])
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
        if (!$algInstance->isActive()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_NO_ACTIVE', ['#TYPE#' => $alg]);
            return $res;
        }
        if (\file_exists($strFilePath)) {
            $res = $algInstance->compress(
                $strFilePath,
                \array_merge(
                    [
                        'changeChmod' => $this->getChmod(
                            Option::get($this->MODULE_ID, 'change_chmod', 777)
                        ),
                    ],
                    $options
                )
            );
        }
        return $res;
    }

    /**
     * Compress image by fileID
     * @param int $intFileID
     * @return bool|null
     */
    public function compressImageByID(int $intFileID)
    {
        $res = false;
        if(!static::$enable || !$intFileID) {
            return null;
        }

        $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforeResizeImage", [$intFileID]);
        $event->send();

        $arFile = \CFile::GetByID($intFileID)->GetNext();
        if (!\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)) {
            return null;
        }

        if (!Check::isActiveByMimeType($arFile["CONTENT_TYPE"])) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_NO_ACTIVE', ['#TYPE#' => $arFile["CONTENT_TYPE"]]);
            return null;
        }

        $strFilePath = $_SERVER["DOCUMENT_ROOT"] . \CFile::GetPath($intFileID);

        if (\file_exists($strFilePath)) {
            $oldSize = $arFile["FILE_SIZE"]; // filesize($strFilePath);
            if ($this->enableImageResize) {
                $this->resize($intFileID, $strFilePath);
            }
            switch ($arFile["CONTENT_TYPE"]) {
                case 'image/jpeg':
                    $isCompress = $this->compressJPG($strFilePath);
                    break;
                case 'image/png':
                    $isCompress = $this->compressPNG($strFilePath);
                    break;
                case 'application/pdf':
                    $isCompress = $this->compressPdf($strFilePath);
                    break;
                case 'image/svg':
                    $isCompress = $this->process(
                        $strFilePath,
                        Option::get($this->MODULE_ID, 'opti_algorithm_svg', '')
                    );
                    break;
                case 'image/gif':
                    $isCompress = $this->process(
                        $strFilePath,
                        Option::get($this->MODULE_ID, 'opti_algorithm_gif', '')
                    );
                    break;
                default:
                    $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_CONTENT_TYPE', [
                        '#TYPE#' => $arFile["CONTENT_TYPE"],
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
                    $res = ImageCompressTable::add($arFields);
                } else {
                    $res = ImageCompressTable::update($intFileID, $arFields);
                }
            } else {
                $this->LAST_ERROR = '';
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
     * @param int $fileId
     * @param string $strFilePath
     * @return bool
     */
    public function resize(int $fileId, string $strFilePath)
    {
        if(!static::$enable) return false;
        if (!$strFilePath) return false;
        if (!$this->enableImageResize) return false;

        $width = Option::get($this->MODULE_ID, 'resize_image_width', '');
        $height = Option::get($this->MODULE_ID, 'resize_image_height', '');
        $algorithm = Option::get($this->MODULE_ID, 'resize_image_algorithm', '');
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
            if ($fileId) {
                $this->saveWidthHeight($fileId, $strFilePath);
            }
            \unlink($destinationFile);
        }
        return $res;
    }

    /**
     * @param int $fileId
     * @param string $filepath
     * @return \CDBResult|false|null
     */
    public function saveWidthHeight(int $fileId, string $filepath)
    {
        global $DB;
        \clearstatcache(true, $filepath);
        $arImageSize = \CFile::GetImageSize($filepath);
        return $DB->Query("UPDATE b_file SET HEIGHT='" . \round((float)$arImageSize[1]) . "', WIDTH='" . \round((float)$arImageSize[0]) . "' WHERE ID=" . $fileId);
    }

    /**
     * Save filesize in table b_file
     * @param int $fileId
     * @param int $newSize
     * @return mixed
     */
    public function saveSizeBitrix(int $fileId, int $newSize)
    {
        global $DB;
        $this->updateSizeDiskModule($fileId, $newSize);
        return $DB->Query("UPDATE b_file SET FILE_SIZE='" . $DB->ForSql($newSize, 255) . "' WHERE ID=" . (int)$fileId);
    }

    /**
     * @param int $intFileID
     * @param array $arFields
     * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function addCompressTable(int $intFileID, array $arFields)
    {
        $rs = ImageCompressTable::getById($intFileID);

        if ($rs->getSelectedRowsCount() <= 0) {
            $res = ImageCompressTable::add($arFields);
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
        if ($instance->enableSection && !empty($arFields['PICTURE']) && is_numeric($arFields['PICTURE'])) {
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
     * CFile::SaveFile()
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

        // resize
        $instance->resize(0, $arFile["tmp_name"]);

        switch ($arFile["type"]) {
            case 'image/jpeg':
                $isCompress = $instance->compressJPG($arFile["tmp_name"]);
                break;
            case 'image/png':
                $isCompress = $instance->compressPNG($arFile["tmp_name"]);
                break;
            case 'application/pdf':
                $isCompress = $instance->compressPdf($arFile["tmp_name"]);
                break;
            case 'image/svg':
                $isCompress = $instance->process(
                    $arFile["tmp_name"],
                    Option::get($instance->MODULE_ID, 'opti_algorithm_svg', '')
                );
                break;
            case 'image/gif':
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
     * Delete picture
     * @param array $arFile
     * @return void
     * @throws \Exception
     */
    public static function CompressImageOnFileDeleteEvent(array $arFile)
    {
        if(!static::$enable) return;
        ImageCompressTable::delete($arFile['ID']);
    }

    /**
     * @param array $arFile
     * @param array $arParams
     * @param $callbackData
     * @param $cacheImageFile
     * @param $cacheImageFileTmp
     * @param $arImageSize
     * @return void|null
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function CompressImageOnResizeEvent(
        $arFile,
        $arParams,
        &$callbackData,
        &$cacheImageFile,
        &$cacheImageFileTmp,
        &$arImageSize
    ) {
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
            case 'image/jpeg':
                $instance->compressJPG($cacheImageFileTmp);
                break;
            case 'image/png':
                $instance->compressPNG($cacheImageFileTmp);
                break;
            case 'application/pdf':
                $instance->compressPdf($cacheImageFileTmp);
                break;
            case 'image/svg':
                $instance->process(
                    $cacheImageFileTmp,
                    Option::get($instance->MODULE_ID, 'opti_algorithm_svg', '')
                );
                break;
            case 'image/gif':
                $instance->process(
                    $cacheImageFileTmp,
                    Option::get($instance->MODULE_ID, 'opti_algorithm_gif', '')
                );
                break;
        }
    }

    /**
     * @param array $arOrder
     * @param array $arFilter
     * @return string
     */
    public function queryBuilder(array $arOrder = [], array $arFilter = []): string
    {
        global $DB;
        $arSqlSearch = [];
        $arSqlOrder = [];
        $strSqlSearch = "";

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
        if (empty($arSqlOrder)) {
            $arSqlOrder[] = "f.ID ASC";
        }
        $strSqlOrder = " ORDER BY " . \implode(", ", $arSqlOrder);

        $strSql =
            "SELECT f.*, " . $DB->DateToCharFunction("f.TIMESTAMP_X") . " as TIMESTAMP_X, tf.* " .
            "FROM b_file f " .
            "LEFT JOIN {$this->tableName} as tf ON f.ID = tf.FILE_ID" .
            $strSqlSearch .
            $strSqlOrder;

        return $strSql;
    }

    /**
     * @param array $arOrder
     * @param array $arFilter
     * @param int $limit
     * @param int $offset
     * @return \CDBResult|false|null
     */
    public function getFileList(array $arOrder = [], array $arFilter = [], int $limit = 100, int $offset = 0)
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

    /**
     * @param int|null $fileSize
     * @param int $digits
     * @return string
     */
    public function getNiceFileSize(?int $fileSize, int $digits = 2): string
    {
        if (!$fileSize) {
            return '';
        }
        $sizes = ["TB", "GB", "MB", "KB", "B"];
        $total = \count($sizes);
        while ($total-- && $fileSize > 1024) {
            $fileSize /= 1024;
        }
        return \round($fileSize, $digits) . " " . $sizes[$total];
    }

    /**
     * @param int $num
     * @return int
     */
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
    public static function setEnable(bool $enable)
    {
        static::$enable = $enable;
    }

    /**
     * Get current state module
     * @return bool
     */
    public static function getEnable(): bool
    {
        return static::$enable;
    }

    /**
     * @param int $fileId
     * @param int $size
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    public function updateSizeDiskModule(int $fileId, int $size)
    {
        if (!self::availableDiskModule()) {
            return;
        }
        global $DB;
        $DB->Query("UPDATE b_disk_object SET SIZE='" . $DB->ForSql($size, 255) . "' WHERE FILE_ID=" . $fileId);
        $DB->Query("UPDATE b_disk_version SET SIZE='" . $DB->ForSql($size, 255) . "' WHERE FILE_ID=" . $fileId);
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public static function availableDiskModule(): bool
    {
        if (self::$hasDisk === null) {
            self::$hasDisk = \Bitrix\Main\Loader::includeModule('disk');
        }
        return self::$hasDisk;
    }

    /**
     * Get IBLOCK_ID by file id
     * @param int $fileId
     * @return int|null
     */
    public function getIblockIdByFileId(int $fileId): ?int
    {
        global $DB;
        $sql = <<<SQL
    SELECT p.IBLOCK_ID FROM b_iblock_property p 
        INNER JOIN b_iblock_element_property e ON e.IBLOCK_PROPERTY_ID=p.ID 
        WHERE p.PROPERTY_TYPE='F' AND e.VALUE={$fileId}
    UNION
    SELECT b.IBLOCK_ID FROM b_iblock_element b WHERE b.PREVIEW_PICTURE={$fileId} OR b.DETAIL_PICTURE={$fileId}
    UNION
    SELECT s.IBLOCK_ID FROM b_iblock_section s WHERE s.PICTURE={$fileId}
SQL;
        $result = $DB->Query($sql);
        if ($result) {
            return (int)($result->Fetch()['IBLOCK_ID'] ?? 0);
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        switch ($this->LAST_ERROR) {
            case 'no_active':
                return Loc::getMessage('DEV2FUN_IMAGECOMPRESS_ERROR_NO_ACTIVE');
        }

        return $this->LAST_ERROR;
    }
}