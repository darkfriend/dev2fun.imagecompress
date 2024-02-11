<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.3
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class ImageCompressImagesConvertedTable extends Entity\DataManager
{
    static $module_id = "dev2fun.imagecompress";

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_d2f_imagecompress_images_converted';
//        return 'b_d2f_imagecompress_webp_images';
    }

    public static function getTableTitle()
    {
        return Loc::getMessage('DEV2FUN_IMAGECOMPRESS_PAGES_TITLE');
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Entity\StringField('IMAGE_PATH', [
                'required' => true,
            ]),

            new Entity\StringField('ORIGINAL_IMAGE_HASH', [
                'required' => true,
            ]),

            new Entity\EnumField('IMAGE_TYPE', [
                'values' => [
                    Convert::TYPE_WEBP,
                    Convert::TYPE_AVIF,
                ],
                'required' => true,
            ]),

        ];
    }

    /**
     * Create table
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function createTable()
    {
        static::getEntity()->createDbTable();
        /** @var Main\DB\MysqlCommonConnection $connection */
        $connection = static::getEntity()->getConnection();

        $connection->createIndex(
            static::getTableName(),
            'unique_image_path_type',
            ['IMAGE_PATH', 'IMAGE_TYPE'],
            $connection::INDEX_UNIQUE
        );
    }

    /**
     * Drop table
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function dropTable()
    {
        static::getEntity()->getConnection()->dropTable(static::getTableName());
    }
}