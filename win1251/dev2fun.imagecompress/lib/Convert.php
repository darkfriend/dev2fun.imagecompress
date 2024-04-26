<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.8.6
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Dev2funImageCompress;

IncludeModuleLangFile(__FILE__);

class Convert
{
    const HIT_CONVERT = 'hitConvert';
    const POST_CONVERT = 'postConvert';
    const LAZY_CONVERT = 'lazyConvert';
    const TYPE_WEBP = 'webp';
    const TYPE_AVIF = 'avif';

    /** @var string */
    private $MODULE_ID = 'dev2fun.imagecompress';
    /** @var string */
    public $LAST_ERROR;
    /** @var string[] support tag attributes */
    public $supportAttrs = [];
    /** @var string[]  */
    public $convertMode = [];
    /** @var int */
    public $cacheTime = 3600;
    /** @var string */
    public $algorithm = 'phpWebp';

    public static $supportContentType = [
        'image/jpeg',
        'image/png',
        'image/svg',
    ];

    public static $convertModes = [
        self::HIT_CONVERT,
        self::POST_CONVERT,
        self::LAZY_CONVERT,
    ];

    public static $convertClasses = [
        'cwebp' => '\Dev2fun\ImageCompress\Webp',
        'phpWebp' => '\Dev2fun\ImageCompress\WebpConvertPhp',
        'phpAvif' => '\Dev2fun\ImageCompress\AvifConvertPhp',
        'imagickAvif' => '\Dev2fun\ImageCompress\AvifConvertImagick',
    ];

    /** @var self */
    private static $instance;
    /** @var bool global state */
    public static $globalEnable = true;
    /** @var null|array */
    public static $domains = null;
    /** @var bool */
    public $enable = false;
    /** @var int */
    public $convertPerPage = 500;
    /** @var int */
    public $cacheTimeFindImages = -1;
    /** @var int */
    public $cacheTimeGetImages = -1;
    /** @var bool */
    public $cacheIncludeUserGroups = true;

    public $siteId;

    /**
     * @param string|null $siteId
     */
    public function __construct(?string $siteId = null)
    {
        if (!$siteId) {
            $siteId = \Dev2funImageCompress::getSiteId();
        }
        $this->siteId = $siteId;

        $this->enable = Option::get($this->MODULE_ID, 'convert_enable', 'N', $siteId) === 'Y';
        $supportAttrs = Option::get($this->MODULE_ID, 'convert_attributes', [], $siteId);
        if($supportAttrs) {
            $supportAttrs = \unserialize($supportAttrs, ['allowed_classes' => false]);
        }
        $this->supportAttrs = $supportAttrs;

        $convertMode = Option::get($this->MODULE_ID, 'convert_mode', [], $siteId);
        if($convertMode) {
            $convertMode = \unserialize($convertMode, ['allowed_classes' => false]);
        } else {
            $convertMode = [self::POST_CONVERT];
        }
        $this->convertMode = $convertMode;

        $this->algorithm = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp', $siteId);

        $this->cacheTime = Option::get($this->MODULE_ID, 'cache_time', 3600, $siteId);

        if (\in_array('lazyConvert', $this->convertMode)) {
            $this->convertPerPage = Option::get($this->MODULE_ID, 'convert_per_page', 500);
            $this->cacheTimeFindImages = Option::get($this->MODULE_ID, 'convert_cache_time_find_images', 3600*24);
            $this->cacheTimeGetImages = Option::get($this->MODULE_ID, 'convert_cache_time_get_images', 3600);
            $this->cacheIncludeUserGroups = Option::get($this->MODULE_ID, 'convert_cache_include_user_groups', 'Y') === 'Y';
        }
    }

    /**
     * @static
     * @return self
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
     * @return Webp|WebpConvertPhp|AvifConvertImagick|AvifConvertPhp
     * @throws \ErrorException
     */
    public static function getAlgInstance(string $algorithm)
    {
        switch ($algorithm) {
            case 'cwebp':
                $obj = Webp::getInstance();
                break;
            case 'phpWebp':
                $obj = WebpConvertPhp::getInstance();
                break;
            case 'imagickAvif':
                $obj = AvifConvertImagick::getInstance();
                break;
            case 'phpAvif':
                $obj = AvifConvertPhp::getInstance();
                break;
            default:
                throw new \ErrorException("Not found algorithm \"{$algorithm}\"");
        }

        return $obj;
//        return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
    }

    /**
     * @param string $algorithm
     * @return string
     */
    public function getImageTypeByAlgorithm(string $algorithm): string
    {
        switch ($algorithm) {
            case 'cwebp':
            case 'phpWebp':
                return 'webp';
            case 'imagickAvif':
            case 'phpAvif':
                return 'avif';
        }

        return '';
    }

    /**
     * Get exclude pages
     * @param string|null $siteId
     * @return array
     */
    public static function getSettingsExcludePage(?string $siteId = null)
    {
        if (!$siteId) {
            $siteId = Dev2funImageCompress::getSiteId();
        }
        $pages = Option::get(\Dev2funImageCompress::MODULE_ID, 'exclude_pages', '', $siteId);
        if ($pages) {
            $pages = \json_decode($pages, true);
        } else {
            $pages = [];
        }
        if (!in_array('#(\/bitrix\/.*)#', $pages)) {
            array_unshift($pages, '#(\/bitrix\/.*)#');
        }
        return $pages;
    }

    /**
     * Get exclude files
     * @param string|null $siteId
     * @return array
     */
    public static function getSettingsExcludeFiles(?string $siteId = null)
    {
        if (!$siteId) {
            $siteId = Dev2funImageCompress::getSiteId();
        }
        $files = Option::get(\Dev2funImageCompress::MODULE_ID, 'exclude_files', '', $siteId);
        if ($files) {
            $files = \json_decode($files, true);
        } else {
            $files = [];
        }
        return $files;
    }

    /**
     * Save exclude pages
     * @param array $sFields
     * @param string $siteId
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function saveSettingsExcludePage(array $sFields = [], string $siteId = 's1')
    {
        if($sFields) {
            $sFields = array_unique($sFields);
            foreach ($sFields as $key => $field) {
                if (empty($field)) {
                    unset($sFields[$key]);
                }
            }
        } elseif(!\is_array($sFields)) {
            $sFields = [];
        }
        Option::set(
            \Dev2funImageCompress::MODULE_ID,
            'exclude_pages',
            \json_encode(\array_values($sFields)),
            $siteId
        );
        return true;
    }

    /**
     * Check page on exclude
     * @return bool
     */
    public static function isExcludePage(): bool
    {
        global $APPLICATION;
        $arExcluded = self::getSettingsExcludePage();
        if($arExcluded) {
            $curPage = $APPLICATION->GetCurPage();
            if ($curPage === '/') {
                $curPage = 'index.php';
            }
            if (in_array(ltrim($curPage, '/'), $arExcluded)) {
                return true;
            }
            foreach ($arExcluded as $exc) {
                if (preg_match($exc, $curPage)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check page on exclude
     * @param string $file
     * @return bool
     */
    public static function isExcludeFile($file): bool
    {
        $file = ltrim($file, '/');
        $arExcluded = self::getSettingsExcludeFiles();

        $arExcludedRegExp = array_filter($arExcluded, function($item) {
            return strpos($item, '#') === 0;
        });

        foreach ($arExcludedRegExp as $item) {
            if (preg_match($item, $file)) {
                return true;
            }
        }

        return in_array($file, $arExcluded);
    }

    /**
     * Save exclude files
     * @param array $sFields
     * @param string $siteId
     * @return void
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function saveSettingsExcludeFile(array $sFields = [], string $siteId = 's1')
    {
        if(!$sFields || !is_array($sFields)) {
            return;
        }
        foreach ($sFields as $key => $field) {
            if (empty($field)) {
                unset($sFields[$key]);
            }
        }
        Option::set(
            \Dev2funImageCompress::MODULE_ID,
            'exclude_files',
            json_encode(array_values($sFields)),
            $siteId
        );
    }

    /**
     * Start process convert
     * @param array $arFile
     * @param array $options
     * @return bool|null|string
     */
    public function process(array $arFile, array $options=[])
    {
        if(!static::$globalEnable || !$this->enable) {
            return false;
        }
        $res = false;

        $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforeConvertImage", [&$arFile]);
        $event->send();
        if($arFile === false) {
            return false;
        }

        if(!static::checkWebpSupport()) {
            return false;
        }

        if (!\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)) {
            return false;
        }

        $alg = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp', $this->siteId);
        $algInstance = static::getAlgInstance($alg);
        if (!$algInstance->isOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => $alg]);
            return $res;
        }

        $upload_dir = Option::get('main', 'upload_dir', 'upload');
        $res = "/$upload_dir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";

        // исключение файла из списка исключений
        if (static::isExcludeFile($res)) {
            return false;
        }

        $strFilePath = $_SERVER["DOCUMENT_ROOT"] . $res;
        if (!\is_file($strFilePath)) {
            return false;
        }

        //        $upload_dir = Option::get('main', 'upload_dir', 'upload');
        //        $src = "{$_SERVER["DOCUMENT_ROOT"]}/$upload_dir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        //        $srcWebp = "/{$upload_dir}/resize_cache/webp/{$arFile["SUBDIR"]}/{$arFile['FILE_NAME']}.webp";

        return $algInstance->convert(
            $arFile,
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

    /**
     * @param string[] $arFiles
     * @param array $options
     * @return array|false
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \ErrorException
     */
    public function postProcess(array $arFiles, array $options=[])
    {
        if(!static::$globalEnable || !$this->enable || !static::checkWebpSupport()) {
            return false;
        }

        $arFilesReplace = [];
        foreach ($arFiles as $file) {
            $resFile = $this->convertFile($file, $options);
            if (!$resFile) {
                continue;
            }
            $arFilesReplace[$file] = $resFile;
        }

        return $arFilesReplace;
    }

    /**
     * @param string $file
     * @param array $options
     * @return bool|string|null
     * @throws \ErrorException
     */
    public function convertFile(string $file, array $options = [])
    {
        $alg = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp', $this->siteId);
        $algInstance = static::getAlgInstance($alg);

        if (!$algInstance->isOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => $alg]);
            return false;
        }

        if($file) {
            $fileScheme = \parse_url($file, \PHP_URL_SCHEME);
            if($fileScheme==='data') {
                return null;
            }
        }

        $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforePostConvertImage", [&$file]);
        $event->send();

        if(!$file) {
            return null;
        }

        // исключение файла из списка исключений
        if (static::isExcludeFile($file)) {
            return null;
        }

        $absFile = "{$_SERVER["DOCUMENT_ROOT"]}{$file}";
        if(!\is_file($absFile)) {
            return null;
        }
        $fileInfo = \pathinfo($absFile);
        $arFile = [
            'CONTENT_TYPE' => \mime_content_type($absFile),
            'SUBDIR' => \str_replace($_SERVER["DOCUMENT_ROOT"], '', $fileInfo['dirname']),
            'FILE_NAME' => $fileInfo['basename'],
            'ABS_PATH' => $absFile,
        ];

        if (!\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)) {
            return null;
        }
        if (!\is_file($absFile)) {
            return null;
        }
        return $algInstance->convert(
            $arFile,
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

    /**
     * Событие на ресайзе (OnAfterResizeImage)
     * @param array $arFile
     * @param array $arInfo
     * @param array $callbackData
     * @param string $cacheImageFile
     * @param string $cacheImageFileTmp
     * @param array $arImageSize
     * @return bool
     * @throws \ErrorException
     */
    public static function CompressImageCacheOnConvertEvent(
        $arFile,
        $arInfo,
        &$callbackData,
        &$cacheImageFile,
        &$cacheImageFileTmp,
        &$arImageSize
    )
    {
        if(
            !static::$globalEnable
            || !self::getInstance()->enable
            || static::isExcludePage()
            || !static::checkWebpSupport()
        ) {
            return false;
        }

        $urlFile = \parse_url($cacheImageFile);
        $uploadDir = Option::get('main', 'upload_dir', 'upload');

        $urlFile['path'] = \str_replace("/$uploadDir/",'',$urlFile['path']);
        $urlFile['path'] = \str_replace("/{$arFile['FILE_NAME']}",'',$urlFile['path']);

        $arFileConvert = [
            'CONTENT_TYPE' => $arFile['CONTENT_TYPE'],
            'SUBDIR' => $urlFile['path'],
            'FILE_NAME' => $arFile['FILE_NAME'],
        ];

        $event = new \Bitrix\Main\Event(self::getInstance()->MODULE_ID, "OnBeforeConvertImageResize", [&$arFileConvert]);
        $event->send();
        if($arFile === false) {
            return false;
        }

        // исключение файла из списка исключений
        $resFile = "/$uploadDir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";
        if (static::isExcludeFile($resFile)) {
            return false;
        }

        // исключение файла из-за типа файла
        if (!\in_array($arFileConvert["CONTENT_TYPE"], static::$supportContentType)) {
            return false;
        }

        $alg = Option::get(self::getInstance()->MODULE_ID, 'convert_algorithm', 'phpWebp', \Dev2funImageCompress::getSiteId());
        $algInstance = static::getAlgInstance($alg);
        if (!$algInstance->isOptim()) {
            return false;
        }

        $strFilePath = $_SERVER["DOCUMENT_ROOT"] . $resFile;
        if (!\is_file($strFilePath)) {
            return false;
        }

        $webpPath = $algInstance->convert(
            $arFileConvert,
            [
                'changeChmod' => self::getInstance()->getChmod(
                    Option::get(self::getInstance()->MODULE_ID, 'change_chmod', 777)
                ),
            ]
        );

        if($webpPath) {
            $cacheImageFile = $webpPath;
            return true;
        }

        return false;
    }

    /**
     * Compress image by fileID
     * @param int $intFileID
     * @return bool|null|string
     */
    public function convertImageByID(int $intFileID)
    {
        if(
            !self::$globalEnable
            || !self::getInstance()->enable
            || !\in_array('hitConvert', self::getInstance()->convertMode)
        ) {
            return false;
        }

        if(!$intFileID) {
            return null;
        }

        // исключение страницы из списка исключений
        if (self::isExcludePage()) {
            return false;
        }

        $arFile = \CFile::GetByID($intFileID)->GetNext();

        //        if ($this->enableImageResize) {
        //            $this->resize($intFileID, $strFilePath);
        //        }

        return $this->process($arFile);
    }

    /**
     * Resize image file
     * @param int $fileId
     * @param string $strFilePath
     * @return bool
     * @deprecated
     */
    public function resize(int $fileId, string $strFilePath): bool
    {
        if(!static::$globalEnable || !$strFilePath || !static::getInstance()->enable) {
            return false;
        }

        $width = Option::get($this->MODULE_ID, 'resize_image_width', '', $this->siteId);
        $height = Option::get($this->MODULE_ID, 'resize_image_height', '', $this->siteId);
        $algorithm = Option::get($this->MODULE_ID, 'resize_image_algorithm', '', $this->siteId);
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
//            Compress::getInstance()->saveWidthHeight($fileId, $strFilePath);
//            unlink($destinationFile);
        }
        return $res;
    }

    /**
     * Handler for OnGetFileSRC
     * @param array $arFile
     * @return bool|null
     */
    public static function CompressImageOnConvertEvent(array $arFile)
    {
        if(
            !static::$globalEnable
            || !static::getInstance()->enable
            || !\in_array('hitConvert', static::getInstance()->convertMode)
        ) {
            return false;
        }

        if(self::isExcludePage()) {
            return false;
        }

        return self::getInstance()->process($arFile);
    }

    /**
     * @return string
     */
    public function getSupportAttributesString(): string
    {
        return $this->supportAttrs
            ? \trim(\implode('|', $this->supportAttrs)).'|'
            : '';
    }

    /**
     * Handler for post converter
     * @param string $content
     * @return bool|null
     */
    public static function PostConverterEvent(string &$content)
    {
        global $APPLICATION, $USER;
        if(!$content) {
            return $content;
        }

        if(
            !self::$globalEnable
            || !static::getInstance()->enable
            || (
                !\in_array('postConvert', self::getInstance()->convertMode)
                && !\in_array('lazyConvert', self::getInstance()->convertMode)
            )
        ) {
            return $content;
        }

        if(self::isExcludePage()) {
            return $content;
        }

        $curUri = $APPLICATION->GetCurUri();
        $userGroups = 'guest';
        $includeUserGroups = self::getInstance()->cacheIncludeUserGroups;
        if ($includeUserGroups && $USER->IsAuthorized()) {
            $userGroups = $USER->GetGroups();
        }

        $cacheId = [
            'v0.1.0',
            'scanImages',
            self::getInstance()->convertMode,
            \Dev2funImageCompress::getSiteId(),
            $userGroups,
            $curUri,
        ];
        $cacheId = implode('|',$cacheId);
        $arFiles = LazyConvert::cache(
        //                3600*1,
            self::getInstance()->cacheTimeGetImages,
            $cacheId,
            '/scan-images',
            function() use ($content) {
                $arFiles = [];
                \preg_match_all('/([^"\'=\s]+\.(?:jpe?g|png))/mi', $content, $matchInlineImages);
                if(!empty($matchInlineImages[1])) {
                    $arFiles = $matchInlineImages[1];
                }

                \preg_match_all('/url\(([^"\'=\s]+\.(?:jpe?g|png))\)/mi', $content, $matchInlineImages);
                if(!empty($matchInlineImages[1])) {
                    $arFiles = \array_merge(
                        $arFiles,
                        $matchInlineImages[1]
                    );
                }

                if ($arFiles) {
                    $arFiles = \array_unique($arFiles);
                }

                if (\in_array(self::LAZY_CONVERT, self::getInstance()->convertMode)) {
                    foreach ($arFiles as $kFile => &$file) {
                        $preparedFile = self::getNormalizePathFile($file);
                        if ($preparedFile === null) {
                            unset($arFiles[$kFile]);
                            continue;
                        }
                        if ($file !== $preparedFile) {
                            $file = $preparedFile;
                        }
                    }
                    unset($file);
                }

                return $arFiles;
            }
        );

        if (!$arFiles) {
            return $content;
        }

        if (\in_array(self::LAZY_CONVERT, self::getInstance()->convertMode)) {

            $jsonFiles = json_encode($arFiles);
            $cacheId = [
                'v0.6.10',
                'findImages',
                $jsonFiles,
            ];
            $cacheId = implode('|',$cacheId);
            LazyConvert::cache(
                self::getInstance()->cacheTimeFindImages,
                    $cacheId,
                '/find-images',
                function () use ($arFiles) {
                    $currentFiles = LazyConvert::findFiles($arFiles);
//                    DebugHelper::print_pre($currentFiles, 1);
                    $connection = \Bitrix\Main\Application::getInstance()->getConnection();
                    $rows = [];
                    foreach ($arFiles as $file) {
                        $isUrl = !empty(parse_url($file, PHP_URL_HOST));
                        if ($isUrl) {
                            $md5 = md5_file($file);
                        } else {
                            $md5 = md5_file($_SERVER['DOCUMENT_ROOT'].$file);
                        }

                        if (empty($currentFiles[$md5])) {
                            $rows[] = [
//                                'SITE_ID' => \Dev2funImageCompress::getSiteId(),
                                'IMAGE_PATH' => $file,
                                'IMAGE_HASH' => $md5,
                                'DATE_CREATE' => new SqlExpression("NOW()"),
                                'IMAGE_IGNORE' => 'N',
                                //                            'IMAGE_PROCESSED' => 'N',
                            ];
                        }
                    }
                    $sql = MySqlHelper::getInsertIgnoreMulti(
                        ImageCompressImagesTable::getTableName(),
                        $rows
                    );
//                    $connection->getSqlHelper()->prepareMerge(
//                        ImageCompressImagesTable::getTableName(),
//                        [],
//                        $rows,
//                    );
                    $connection->queryExecute($sql);
                    return true;
                },
                false
            );

            if (!static::checkWebpSupport()) {
                return $content;
            }

            $cacheId = [
                'v0.1.5',
                'getImages',
                $jsonFiles,
            ];
            $cacheId = implode('|',$cacheId);
            $arFileReplace = LazyConvert::cache(
//                3600*1,
                self::getInstance()->cacheTimeGetImages,
                $cacheId,
                '/get-images',
                function () use ($arFiles) {
                    $filter = [
//                        'IMAGE_PROCESSED' => 'Y',
                        'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED' => 'Y',
                        'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE' => self::getInstance()->getImageTypeByAlgorithm(
                            self::getInstance()->algorithm
                        ),
                        'IMAGE_IGNORE' => 'N',
                    ];
                    $imagesHash = [];
                    foreach ($arFiles as $file) {
                        $isUrl = !empty(parse_url($file, PHP_URL_HOST));
                        if ($isUrl) {
                            $imagesHash[] = md5_file($file);
                        } else {
                            $imagesHash[] = md5_file($_SERVER['DOCUMENT_ROOT'].$file);
                        }
//                        $imagesHash[] = md5_file($_SERVER['DOCUMENT_ROOT'].$file);
//                        $rows[] = [
//                            'SITE_ID' => \Dev2funImageCompress::getSiteId(),
//                            'IMAGE_PATH' => $file,
//                            'IMAGE_HASH' => md5_file($_SERVER['DOCUMENT_ROOT'].$file),
//                        ];
                    }
                    if (!$imagesHash) {
                        return [];
                    }
                    $filter[] = [
                        'IMAGE_HASH', 'in', $imagesHash,
//                            'logic' => Filter::LOGIC_OR,
//                            ['IMAGE_HASH', 'in', ''],
                    ];
                    $images = ImageCompressImagesTable::getList([
                            'select' => [
                                '*',
                                'CONVERTED_IMAGE_PATH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.IMAGE_PATH',
                                'CONVERTED_IMAGE_ID' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ID',
                                'CONVERTED_IMAGE_HASH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ORIGINAL_IMAGE_HASH',
                                'IMAGE_TYPE' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.IMAGE_TYPE',
//                                'IMAGE_PROCESSED' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED',
//                                'REF_WEBP_' => 'WEBP_IMAGE',
//                                'WEBP_' => 'WEBP_PATH',
                            ],
                            'filter' => $filter,
                        ])
                        ->fetchAll();

                    $result = [];
                    foreach ($images as $image) {
                        $result[$image['IMAGE_PATH']] = $image['CONVERTED_IMAGE_PATH'];
                    }

                    return $result;
                }
            );

            if($arFileReplace) {
                $content = \strtr($content, $arFileReplace);
            }

        } else {
            if (!static::checkWebpSupport()) {
                return $content;
            }

            $arFileReplace = self::getInstance()->postProcess($arFiles);
            if($arFileReplace) {
                $event = new \Bitrix\Main\Event(
                    self::getInstance()->MODULE_ID,
                    "OnBeforePostConvertReplaceImage",
                    [&$arFileReplace]
                );
                $event->send();

                if($arFileReplace) {
                    $content = \strtr($content, $arFileReplace);
                }
            }
        }

        return $content;
    }

    /**
     * @return array
     */
    public static function getDomains(): array
    {
        if (self::$domains === null) {
            $sites = \Dev2funImageCompress::getSites();
            $domains = [];
            foreach ($sites as $site) {
                $domains[] = $site['SERVER_NAME'];
                $siteDomains = !empty($site['DOMAINS'])
                    ? explode("\n", $site['DOMAINS'])
                    : [];
                if ($siteDomains) {
                    foreach ($siteDomains as $siteDomain) {
                        $domains[] = $siteDomain;
                    }
                }
            }
            if ($domains) {
                $domains = array_unique($domains);
            }
            self::$domains = $domains;
        }
        return self::$domains;
    }

    /**
     * Return normalized path to file or null for exclude
     * @param string $file
     * @return string|null
     */
    public static function getNormalizePathFile(string $file): ?string
    {
        $url = \parse_url($file);
        if (empty($url['host'])) {
            return $file;
        }
        if ($url['host'] === 'data') {
            return null;
        }
        $hosts = array_filter(
            self::getDomains(),
            function($hostVal) use ($file) {
                return strpos($file, $hostVal) !== false;
            }
        );
        if (!$hosts) {
            return $file;
        }
        $replacer = [
            '://',
            '//',
            $url['host'],
        ];
        if (!empty($url['scheme'])) {
            $replacer[] = $url['scheme'];
        }
        return str_replace($replacer, '', $file);
    }

    /**
     * Get normalize file size
     * @param int $fileSize
     * @param int $digits
     * @return string
     */
    public function getNiceFileSize(int $fileSize, int $digits = 2)
    {
        $sizes = ["TB", "GB", "MB", "KB", "B"];
        $total = \count($sizes);
        while ($total-- && $fileSize > 1024) {
            $fileSize /= 1024;
        }
        return \round($fileSize, $digits) . " " . $sizes[$total];
    }

    /**
     * Get normalize chmod value
     * @param int $num
     * @return int
     */
    public function getChmod(int $num)
    {
        if (!$num) return 0777;
        switch ($num) {
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
        static::$globalEnable = $enable;
    }

    /**
     * Get current state module
     * @return bool
     */
    public static function getEnable(): bool
    {
        return static::$globalEnable;
    }

    /**
     * Check current path on support webp
     * @return bool
     */
    public static function checkSupportWebpCurrentPath(): bool
    {
        global $APPLICATION;
        return !\preg_match('#\/bitrix\/admin\/#', $APPLICATION->GetCurPage());
    }

    /**
     * Check header accept on support webp
     * @return bool
     */
    public static function checkSupportWebpAccept(): bool
    {
        return \strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }

    /**
     * Get result check webp support
     * @return bool
     */
    public static function checkWebpSupport(): bool
    {
        if(!static::checkSupportWebpCurrentPath()) {
            return false;
        }

        $curBrowserAgent = $_SERVER["HTTP_USER_AGENT"] ?? '';
        $supportBrowsers = [
            'chrome',
            'opera',
        ];
        $event = new \Bitrix\Main\Event(
            self::getInstance()->MODULE_ID,
            'OnBeforeCheckWebpBrowserSupport',
            [&$supportBrowsers, &$curBrowserAgent]
        );
        $event->send();

        if (!$curBrowserAgent) {
            return true;
        }

        $result = \in_array(self::getBrowserAgentName($curBrowserAgent), $supportBrowsers)
            || self::checkSupportWebpAccept();

        $event = new \Bitrix\Main\Event(self::getInstance()->MODULE_ID, "OnAfterCheckWebpSupport", [$result]);
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $evenResult) {
                if ($evenResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $result = (bool) $evenResult->getParameters();
                }
            }
        }

        return (bool)$result;
    }

    /**
     * Get browser name
     * @param string|null $userAgent
     * @return string
     */
    public static function getBrowserAgentName(?string $userAgent): string
    {
        $result = 'Other';
        if(!$userAgent) {
            return $result;
        }
        $userAgent = \mb_strtolower($userAgent);
        switch ($userAgent) {
            case \strpos($userAgent, 'opera')!==false || \strpos($userAgent, 'opr/')!==false:
                $result = 'opera';
                break;
            case \strpos($userAgent, 'edge')!==false:
                $result = 'edge';
                break;
            case \strpos($userAgent, 'chrome')!==false:
                $result = 'chrome';
                break;
            case \strpos($userAgent, 'safari')!==false:
                $result = 'safari';
                break;
            case \strpos($userAgent, 'firefox')!==false:
                $result = 'firefox';
                break;
            case \strpos($userAgent, 'msie')!==false || \strpos($userAgent, 'trident/7')!==false:
                $result = 'msie';
                break;
        }

        return $result;
    }

    /**
     * Get original src by webp src
     * @param string $srcWebp
     * @param string|null $siteId
     * @return string
     */
    public static function getOriginalSrc(string $srcWebp, ?string $siteId = null): string
    {
        if (!$siteId) {
            $siteId = Dev2funImageCompress::getSiteId();
        }
        $alg = Option::get(self::getInstance()->MODULE_ID, 'convert_algorithm', 'phpWebp', $siteId);
        $algInstance = static::getAlgInstance($alg);
        return $algInstance ? $algInstance->getOriginalSrc($srcWebp) : '';
    }

    /**
     * Convert image by absolute or relative path
     * @param string $file
     * @param array $options
     * @return bool|string|null
     */
    public function convertImageByPath(string $file, array $options=[])
    {
        if(!static::$globalEnable || !$this->enable || !$file) {
            return null;
        }

        $fileScheme = \parse_url($file, \PHP_URL_SCHEME);
        if ($fileScheme === 'data') {
            return null;
        }

        if (strpos($file, $_SERVER["DOCUMENT_ROOT"]) !== 0) {
            $absFile = "{$_SERVER["DOCUMENT_ROOT"]}$file";
        } else {
            $absFile = $file;
        }

        if(!\is_file($absFile)) {
            return null;
        }
        $fileInfo = \pathinfo($absFile);
        $arFile = [
            'CONTENT_TYPE' => \mime_content_type($absFile),
            'SUBDIR' => \str_replace($_SERVER["DOCUMENT_ROOT"], '', $fileInfo['dirname']),
            'FILE_NAME' => $fileInfo['basename'],
            'ABS_PATH' => $absFile,
        ];

        return $this->process($arFile, $options);
    }

//    public function getList(
//        array $arOrder = [],
//        array $arFilter = [],
//        int $limit = 100,
//        int $offset = 0
//    ) {
//        global $DB;
//        $strSql = $this->queryBuilder($arOrder, $arFilter);
//        //        if($limit) {
//        //            $strSql .= ' LIMIT '.$limit;
//        //        }
//        //
//        //        if($offset) {
//        //            $strSql .= ' OFFSET '.$offset;
//        //        }
//        return $DB->Query($strSql, false, "FILE: " . __FILE__ . "<br> LINE: " . __LINE__);
//    }
}