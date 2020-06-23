<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.5.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class Convert
{
    private $MODULE_ID = 'dev2fun.imagecompress';
    public $LAST_ERROR;

    public static $supportContentType = [
        'image/jpeg',
        'image/png',
//        'application/pdf',
        'image/svg',
//        'image/gif',
    ];

    public static $convertClasses = [
        'cwebp' => '\Dev2fun\ImageCompress\Webp',
        'phpWebp' => '\Dev2fun\ImageCompress\WebpConvertPhp',
    ];

    private static $instance;
    /** @var bool state */
    public static $enable = false;

    private function __construct()
    {
        static::$enable = Option::get($this->MODULE_ID, 'convert_enable', 'N') === 'Y';
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
     * @return null|Webp|WebpConvertPhp
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
        }
        return $obj;
//        return self::$optiClasses[$algorithm]::getInstance(); // PHP7+
    }

    /**
     * Запускает процесс
     * @param array $arFile
     * @param array $options
     * @return bool|null
     */
    public function process($arFile, $options=[])
    {
        if(!static::$enable) return null;
        $res = null;

        $event = new \Bitrix\Main\Event($this->MODULE_ID, "OnBeforeConvertImage", [&$arFile]);
        $event->send();
        if($arFile === false) {
            return null;
        }

        if(!static::checkWebpSupport()) {
            return null;
        }

        if (!\in_array($arFile["CONTENT_TYPE"], static::$supportContentType)) {
            return null;
        }

        $alg = Option::get($this->MODULE_ID, 'convert_algorithm', 'phpWebp');
        $algInstance = static::getAlgInstance($alg);
        if (!$algInstance->isOptim()) {
            $this->LAST_ERROR = Loc::getMessage('DEV2FUN_IMAGECOMPRESS_NO_MODULE', ['#MODULE#' => $alg]);
            return $res;
        }

        $upload_dir = Option::get('main', 'upload_dir', 'upload');
        $res = "/$upload_dir/{$arFile["SUBDIR"]}/{$arFile["FILE_NAME"]}";

        $strFilePath = $_SERVER["DOCUMENT_ROOT"] . $res;
        if (!\is_file($strFilePath)) {
            return null;
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
     * Compress image by fileID
     * @param integer $intFileID
     * @return bool|null
     */
    public function convertImageByID($intFileID)
    {
        if(!static::$enable) return null;
        if(!$intFileID) return null;

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
        if(!static::$enable) return false;
        if (!$strFilePath) return false;

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

    public static function CompressImageOnConvertEvent($arFile)
    {
        return self::getInstance()->process($arFile);
    }

    public function getNiceFileSize($fileSize, $digits = 2)
    {
        $sizes = ["TB", "GB", "MB", "KB", "B"];
        $total = count($sizes);
        while ($total-- && $fileSize > 1024) {
            $fileSize /= 1024;
        }
        return round($fileSize, $digits) . " " . $sizes[$total];
    }

    public function getChmod($num)
    {
        if (!$num) return 0777;
        $num = intval($num);
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

    public static function checkWebpSupport()
    {
        global $APPLICATION;
        if (\preg_match('#\/bitrix\/#', $APPLICATION->GetCurPage())) {
            return false;
        }
        if (\strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) {
            return true;
        }
        return \in_array(
            self::getBrowserAgentName($_SERVER["HTTP_USER_AGENT"]),
            [
                'chrome',
                'opera',
            ]
        );
    }

    public static function getBrowserAgentName($userAgent)
    {
        $result = 'Other';
        $userAgent = \mb_strtolower($userAgent);
        switch ($userAgent) {
            case \strpos($userAgent, 'opera') || \strpos($userAgent, 'opr/'):
                $result = 'opera';
                break;
            case \strpos($userAgent, 'edge'):
                $result = 'edge';
                break;
            case \strpos($userAgent, 'chrome'):
                $result = 'chrome';
                break;
            case \strpos($userAgent, 'safari'):
                $result = 'safari';
                break;
            case \strpos($userAgent, 'firefox'):
                $result = 'firefox';
                break;
            case strpos($userAgent, 'msie') || strpos($userAgent, 'trident/7'):
                $result = 'msie';
                break;
        }

        return $result;
    }
}