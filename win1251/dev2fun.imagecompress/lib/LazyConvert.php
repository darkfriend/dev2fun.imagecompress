<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.3
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main\DB\SqlExpression;
use ErrorException;

class LazyConvert
{
    /**
     * Agent runner
     * @return string
     */
    public static function agentRun(): string
    {
        try {
            if (\in_array('lazyConvert', Convert::getInstance()->convertMode)) {
                self::beginConvert();
            }
        } catch (\Throwable $e) {
            \CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'LAZY_AGENT',
                'MODULE_ID' => \Dev2funImageCompress::MODULE_ID,
                'DESCRIPTION' => print_r(
                    [
                        'MESSAGE' => $e->getMessage(),
                        'FILE' => "{$e->getFile()}:{$e->getLine()}",
                    ],
                    true
                ),
            ]);
        }

        return self::class . '::agentRun();';
    }

    /**
     * @return void
     * @throws ErrorException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Throwable
     */
    public static function beginConvert()
    {
        $instance = Convert::getInstance();
        $currentImageType = $instance->getImageTypeByAlgorithm($instance->algorithm);
        $images = \Dev2fun\ImageCompress\ImageCompressImagesTable::getList([
            'select' => [
                '*',
                'CONVERTED_IMAGE_PATH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.IMAGE_PATH',
                'CONVERTED_IMAGE_ID' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ID',
                'CONVERTED_IMAGE_HASH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ORIGINAL_IMAGE_HASH',
                'CONVERTED_IMAGE_PROCESSED' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED',
            ],
            'filter' => [
                '!=Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED' => 'Y',
//                [
//                    'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_PROCESSED', '!=', 'Y',
//                ],
                '!=Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE'  => $currentImageType === Convert::TYPE_WEBP
                    ? Convert::TYPE_AVIF
                    : Convert::TYPE_WEBP,
                '!=IMAGE_IGNORE' => 'Y',
//                [
//                    'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE',
//                    '!=',
////                    $currentImageType === 'webp' ? 'avif' : 'webp',
//
//                ],
//                'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE' => $instance->getImageTypeByAlgorithm($instance->algorithm),
        //        'IMAGE_PROCESSED' => 'Y',
            ],
            'limit' => $instance->convertPerPage,
        ])->fetchAll();

        if (!$images) {
            return;
        }

//        DebugHelper::print_pre($images, 1);

        $event = new \Bitrix\Main\Event(
            \Dev2funImageCompress::MODULE_ID,
            "OnBeforeLazyConvert",
            [&$images]
        );
        $event->send();

        self::convertItems($images);
    }

    /**
     * @param array $images
     * @return void
     * @throws ErrorException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Throwable
     */
    public static function convertItems(array $images)
    {
        $instance = Convert::getInstance();
        $algImageType = $instance->getImageTypeByAlgorithm($instance->algorithm);

        $imagesHash = array_column($images, 'IMAGE_HASH');
        $duplicateImages = ImageCompressImagesConvertedTable::getList([
            'select' => ['ID', 'ORIGINAL_IMAGE_HASH'],
            'filter' => [
                'ORIGINAL_IMAGE_HASH' => $imagesHash,
                'IMAGE_TYPE' => $algImageType,
            ],
        ]);
        $arDuplicateImages = [];
        if ($duplicateImages) {
            foreach ($duplicateImages as $duplicateImage) {
                $arDuplicateImages[$duplicateImage['ORIGINAL_IMAGE_HASH']] = $duplicateImage['ID'];
            }
            unset($duplicateImages);
        }
        foreach ($images as $arImage) {
            self::convertItem($arImage, $algImageType, $arDuplicateImages);
        }
    }

    /**
     * Конвертация каждой позиции
     * @param array $arImage
     * @param string $algImageType
     * @param array $arDuplicateImages
     * @return void
     * @throws ErrorException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Throwable
     */
    public static function convertItem(array $arImage, string $algImageType, array &$arDuplicateImages = [])
    {
        global $DB;
//        \darkfriend\helpers\DebugHelper::print_pre($arImage);
        $convertedFile = Convert::getInstance()->convertFile($arImage['IMAGE_PATH']);
//        \darkfriend\helpers\DebugHelper::print_pre($convertedFile);
//        \darkfriend\helpers\DebugHelper::print_pre(Convert::getInstance()->LAST_ERROR, 1);
        if (!$convertedFile) {
            ImageCompressImagesTable::update($arImage['ID'], [
                'IMAGE_IGNORE' => 'Y',
                'DATE_UPDATE' => new SqlExpression("NOW()"),
                'DATE_CHECK' => new SqlExpression("NOW()"),
            ]);
            return;
        }

        try {
            $DB->StartTransaction();
            if (empty($arDuplicateImages[$arImage['IMAGE_HASH']])) {
                $resWebp = ImageCompressImagesConvertedTable::add([
                    'IMAGE_PATH' => $convertedFile,
                    'ORIGINAL_IMAGE_HASH' => $arImage['IMAGE_HASH'],
                    'IMAGE_TYPE' => $algImageType,
                ]);
                if (!$resWebp->isSuccess()) {
                    throw new ErrorException($resWebp->getErrorMessages()[0]);
                }
                $resConvertedId = $resWebp->getId();
                $arDuplicateImages[$arImage['IMAGE_HASH']] = $resConvertedId;
            } else {
                $resConvertedId = $arDuplicateImages[$arImage['IMAGE_HASH']];
            }

            if (empty($arImage['REF_IMAGE_ID'])) {
                $res = ImageCompressImagesToConvertedTable::add([
                    'IMAGE_ID' => $arImage['ID'],
                    'CONVERTED_IMAGE_ID' => $resConvertedId,
                    //                'CONVERTED_IMAGE_ID' => $resWebp->getId(),
                    'IMAGE_TYPE' => $algImageType,
                    'IMAGE_PROCESSED' => 'Y',
                ]);
                if (!$res->isSuccess()) {
                    throw new ErrorException($res->getErrorMessages()[0]);
                }
            }

            $res = ImageCompressImagesTable::update($arImage['ID'], [
                'IMAGE_IGNORE' => 'N',
                'DATE_UPDATE' => new SqlExpression("NOW()"),
                'DATE_CHECK' => new SqlExpression("NOW()"),
            ]);
            if (!$res->isSuccess()) {
                throw new ErrorException($res->getErrorMessages()[0]);
            }

            $DB->Commit();
        } catch (\Throwable $e) {
            $DB->Rollback();
            throw $e;
        }
    }

    /**
     * @param int $ttl
     * @param string $cacheId
     * @param string $dir
     * @param callable $callable
     * @param bool $returnValue
     * @return null|mixed
     */
    public static function cache(int $ttl, string $cacheId, string $dir, callable $callable, bool $returnValue = true)
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if (method_exists($cache, 'noOutput')) {
            $cache->noOutput();
        }
        try {
            if ($cache->initCache($ttl, $cacheId, \Dev2funImageCompress::MODULE_ID . $dir)) {
                return $returnValue ? $cache->getVars() : null;
            } elseif ($cache->startDataCache()) {
                $result = $callable($cache);
                if (isset($result)) {
                    $cache->endDataCache($result);
                } else {
                    $cache->abortDataCache();
                }
                return $result;
            }
        } catch (\Throwable $e) {
//            DebugHelper::print_pre($e->getMessage(), 1);
        }

        return null;
    }

    /**
     * @param array $arFiles
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function findFiles(array $arFiles = []): array
    {
        if (!$arFiles) {
            return $arFiles;
        }
        $filter = [
            'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.IMAGE_TYPE' => Convert::getInstance()->getImageTypeByAlgorithm(
                Convert::getInstance()->algorithm
            ),
        ];
        $imagesHash = [];
        foreach ($arFiles as $file) {
            $isUrl = !empty(parse_url($file, PHP_URL_HOST));
            if ($isUrl) {
                $imagesHash[] = md5_file($file);
            } else {
                $imagesHash[] = md5_file($_SERVER['DOCUMENT_ROOT'].$file);
            }
        }
        if (!$imagesHash) {
            return [];
        }
        $filter[] = [
            'IMAGE_HASH', 'in', $imagesHash,
        ];
        $images = ImageCompressImagesTable::getList([
                'select' => [
                    'CONVERTED_IMAGE_HASH' => 'Dev2fun\ImageCompress\ImageCompressImagesToConvertedTable:IMAGE.CONVERTED_IMAGE.ORIGINAL_IMAGE_HASH',
                ],
                'filter' => $filter,
            ])
            ->fetchAll();

        $result = [];
        foreach ($images as $image) {
            if (empty($image['CONVERTED_IMAGE_HASH'])) {
                continue;
            }
            $result[$image['CONVERTED_IMAGE_HASH']] = true;
        }

        return $result;
    }
}