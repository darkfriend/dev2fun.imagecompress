<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.7.5
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Convert
{
    private $MODULE_ID = 'dev2fun.imagecompress';
    /** @var string */
    public $LAST_ERROR;
    /** @var string[] support tag attributes */
    public $supportAttrs = [];
    /** @var string[]  */
    public $convertMode = [];
    /** @var int */
    public $cacheTime = 3600;

    public static $supportContentType = [
        'image/jpeg',
        'image/png',
        'image/svg',
    ];

    public static $convertModes = [
        'hitConvert',
        'postConvert',
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
    /** @var bool */
    public $enable = false;

    private function __construct()
    {
        $this->enable = Option::get($this->MODULE_ID, 'convert_enable', 'N') === 'Y';
        $supportAttrs = Option::get($this->MODULE_ID, 'convert_attributes', []);
        if($supportAttrs) {
            $supportAttrs = \unserialize($supportAttrs, ['allowed_classes' => false]);
        }
        $this->supportAttrs = $supportAttrs;

        $convertMode = Option::get($this->MODULE_ID, 'convert_mode');
        if($convertMode) {
            $convertMode = \unserialize($convertMode, ['allowed_classes' => false]);
        } else {
            $convertMode = ['postConvert'];
        }
        $this->convertMode = $convertMode;

        $this->cacheTime = Option::get($this->MODULE_ID, 'cache_time', 3600);
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
     * @return null|Webp|WebpConvertPhp|AvifConvertImagick|AvifConvertPhp
     */
    public static function getAlgInstance($algorithm)
    {
        $obj = null;
        switch ($algorithm) {
            case 'cwebp':
                $obj = \Dev2fun\ImageCompress\Webp::getInstance();
                break;
            case 'phpWebp':
                $obj = \Dev2fun\ImageCompress\WebpConvertPhp::getInstance();
                break;
            case 'imagickAvif':
                $obj = \Dev2fun\ImageCompress\AvifConvertImagick::getInstance();
                break;
            case 'phpAvif':
                $obj = \Dev2fun\ImageCompress\AvifConvertPhp::getInstance();
                break;
        }

        return $obj;
//        return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
    }

    /**
     * Get exclude pages
     * @return array
     */
    public static function getSettingsExcludePage()
    {
        $pages = Option::get(\Dev2funImageCompress::MODULE_ID, 'exclude_pages');
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
     * @return array
     */
    public static function getSettingsExcludeFiles()
    {
        $files = Option::get(\Dev2funImageCompress::MODULE_ID, 'exclude_files');
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
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function saveSettingsExcludePage($sFields = [])
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
            \json_encode(\array_values($sFields))
        );
        return true;
    }

    /**
     * Check page on exclude
     * @return bool
     */
    public static function isExcludePage()
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
    public static function isExcludeFile($file)
    {
        $file = ltrim($file, '/');
        $arExcluded = self::getSettingsExcludeFiles();

        $arExcludedRegExp = array_filter($arExcluded, function($item) {
            return strpos($item, '#');
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
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function saveSettingsExcludeFile($sFields = [])
    {
        if($sFields) {
            foreach ($sFields as $key => $field) {
                if (empty($field)) {
                    unset($sFields[$key]);
                }
            }
        } elseif(!is_array($sFields)) {
            $sFields = [];
        }
        Option::set(
            \Dev2funImageCompress::MODULE_ID,
            'exclude_files',
            json_encode(array_values($sFields))
        );
        return true;
    }

    /**
     * Start process convert
     * @param array $arFile
     * @param array $options
     * @return bool|null|string
     */
    public function process($arFile, $options=[])
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

        $alg = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp');
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
                    'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
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
    public function postProcess($arFiles, $options=[])
    {
        if(!static::$globalEnable || !$this->enable || !static::checkWebpSupport()) {
            return false;
        }

        $alg = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp');
        $algInstance = static::getAlgInstance($alg);
        if (!$algInstance->isOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => $alg]);
            return false;
        }

        $arFilesReplace = [];
        foreach ($arFiles as $file) {
            if($file) {
                $fileScheme = \parse_url($file, \PHP_URL_SCHEME);
                if($fileScheme==='data') {
                    continue;
                }
            }

            $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforePostConvertImage", [&$file]);
            $event->send();

            if(!$file) {
                continue;
            }

            // исключение файла из списка исключений
            if (static::isExcludeFile($file)) {
                continue;
            }

            $absFile = "{$_SERVER["DOCUMENT_ROOT"]}$file";
            if(!\is_file($absFile)) continue;
            $fileInfo = \pathinfo($absFile);
            $arFile = [
                'CONTENT_TYPE' => \mime_content_type($absFile),
                'SUBDIR' => \str_replace($_SERVER["DOCUMENT_ROOT"], '', $fileInfo['dirname']),
                'FILE_NAME' => $fileInfo['basename'],
                'ABS_PATH' => $absFile,
            ];

            if (!\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)) {
                continue;
            }
            if (!\is_file($absFile)) {
                continue;
            }

            $resFile = $algInstance->convert(
                $arFile,
                \array_merge(
                    [
                        'changeChmod' => $this->getChmod(Option::get($this->MODULE_ID, 'change_chmod', 777)),
                    ],
                    $options
                )
            );
            if($resFile) {
                $arFilesReplace[$file] = $resFile;
            }
        }

        return $arFilesReplace;
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

        $alg = Option::get(self::getInstance()->MODULE_ID, 'convert_algorithm', 'phpWebp');
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
                'changeChmod' => self::getInstance()->getChmod(Option::get(self::getInstance()->MODULE_ID, 'change_chmod', 777)),
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
     * @param integer $intFileID
     * @return bool|null|string
     */
    public function convertImageByID($intFileID)
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
     * @param integer $fileId
     * @param string $strFilePath
     * @return bool
     */
    public function resize($fileId, $strFilePath)
    {
        if(!static::$globalEnable || !$strFilePath || !static::getInstance()->enable) {
            return false;
        }

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
            chmod($destinationFile, 0777);
            copy($destinationFile, $strFilePath);
            $this->saveWidthHeight($fileId, $strFilePath);
            unlink($destinationFile);
        }
        return $res;
    }

    /**
     * Handler for OnGetFileSRC
     * @param array $arFile
     * @return bool|null
     */
    public static function CompressImageOnConvertEvent($arFile)
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

    public function getSupportAttributesString()
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
    public static function PostConverterEvent(&$content)
    {
        if(!$content) {
            return $content;
        }

        if(
            !self::$globalEnable
            || !static::getInstance()->enable
            || !\in_array('postConvert', self::getInstance()->convertMode)
        ) {
            return $content;
        }

        if(self::isExcludePage()) {
            return $content;
        }

        if(!static::checkWebpSupport()) {
            return $content;
        }

//        global $APPLICATION, $USER;

        $moduleId = self::getInstance()->MODULE_ID;
//        $curPage = $APPLICATION->GetCurPage();
//        $domain = $_SERVER['HTTP_HOST'];
//        if (!$domain) $domain = \SITE_ID;

//        $obCache = new \CPHPCache();
//        $cachePath = "/{$moduleId}/{$domain}/";
//        $cacheId = \md5(
//            $domain
//            . $curPage
//            . \LANGUAGE_ID
//            . $_SERVER['HTTP_USER_AGENT']
//            . $_SERVER['REQUEST_METHOD']
//            . implode($_REQUEST)
//            . $USER->GetUserGroupString()
//        );
//        $cacheTime = self::getInstance()->cacheTime;
//        if(!$cacheTime) $cacheTime = 3600;

//        if ($USER->IsAdmin() && !empty($_REQUEST['clear_cache'])) {
//            $obCache->Clean($cacheId, $cachePath);
//        }

        $arFileReplace = [];
//        if ($obCache->InitCache($cacheTime, $cacheId, $cachePath)) {
//            $cacheData = $obCache->GetVars();
//            if(!empty($cacheData['files'])) {
//                $arFileReplace = $cacheData['files'];
//            }
//        } elseif ($obCache->StartDataCache()) {
        $arFiles = [];
        \preg_match_all('/([^"\'=\s]+\.(?:jpe?g|png))/mi', $content, $matchInlineImages);
//        \preg_match_all('/url\([\'|"](.*?(?:png|jpg|jpeg))[\'|"]\)/mi', $content, $matchInlineImages);
        if(!empty($matchInlineImages[1])) {
            $arFiles = $matchInlineImages[1];
        }

        \preg_match_all('/url\(([^"\'=\s]+\.(?:jpe?g|png))\)/mi', $content, $matchInlineImages);
        if(!empty($matchInlineImages[1])) {
            $arFiles = \array_unique(\array_merge(
                $arFiles,
                $matchInlineImages[1]
            ));
        }
//        \preg_match_all(
//            '/(?:'.self::getInstance()->getSupportAttributesString().'src)=[\'|"](.*?(?:png|jpg|jpeg)?)[\'|"]/mi',
//            $content,
//            $matchTags
//        );
//        if(!empty($matchTags[1])) {
//            \preg_match_all(
//                '/^(.*?\.(?:jpg|png|jpeg))(?:\?.*?|$)$/mi',
//                \implode(\PHP_EOL, $matchTags[1]),
//                $matchTagImages
//            );
//            if(!empty($matchTagImages[1])) {
//                $arFiles = \array_merge(
//                    $arFiles,
//                    $matchTagImages[1]
//                );
//            }
//        }
        $event = new \Bitrix\Main\Event($moduleId, "OnBeforePostConvertImage", [&$arFiles]);
        $event->send();

        if($arFiles) {
            $arFileReplace = self::getInstance()->postProcess($arFiles);
        }

//            $obCache->EndDataCache([
//                'files' => $arFileReplace,
//            ]);
//        }

        if($arFileReplace) {
            $event = new \Bitrix\Main\Event(
                $moduleId,
                "OnBeforePostConvertReplaceImage",
                [&$arFileReplace]
            );
            $event->send();

            if($arFileReplace) {
                $content = \strtr($content, $arFileReplace);
            }
        }

        return $content;
    }

    /**
     * Get normalize file size
     * @param int $fileSize
     * @param int $digits
     * @return string
     */
    public function getNiceFileSize($fileSize, $digits = 2)
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
     * @param string|int $num
     * @return int
     */
    public function getChmod($num)
    {
        if (!$num) return 0777;
        $num = \intval($num);
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
    public static function setEnable($enable)
    {
        static::$globalEnable = $enable;
    }

    /**
     * Get current state module
     * @return bool
     */
    public static function getEnable()
    {
        return static::$globalEnable;
    }

    /**
     * Check current path on support webp
     * @return bool
     */
    public static function checkSupportWebpCurrentPath()
    {
        global $APPLICATION;
        return !\preg_match('#\/bitrix\/admin\/#', $APPLICATION->GetCurPage());
    }

    /**
     * Check header accept on support webp
     * @return bool
     */
    public static function checkSupportWebpAccept()
    {
        return \strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }

    /**
     * Get result check webp support
     * @return bool
     */
    public static function checkWebpSupport()
    {
        if(!static::checkSupportWebpCurrentPath()) {
            return false;
        }

        $supportBrowsers = [
            'chrome',
            'opera',
        ];
        $event = new \Bitrix\Main\Event(
            self::getInstance()->MODULE_ID,
            'OnBeforeCheckWebpBrowserSupport',
            [&$supportBrowsers]
        );
        $event->send();

        $result = \in_array(self::getBrowserAgentName($_SERVER["HTTP_USER_AGENT"]), $supportBrowsers)
            || self::checkSupportWebpAccept();

        $event = new \Bitrix\Main\Event(self::getInstance()->MODULE_ID, "OnAfterCheckWebpSupport", [$result]);
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $evenResult) {
                if ($evenResult->getResultType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $result = (bool) $evenResult->getParameters();
                }
            }
        }

        return (bool) $result;
    }

    /**
     * Get browser name
     * @param string $userAgent
     * @return string
     */
    public static function getBrowserAgentName($userAgent)
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
}