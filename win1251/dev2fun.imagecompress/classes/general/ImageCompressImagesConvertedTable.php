<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

class ImageCompressImagesConvertedTable extends Entity\DataManager
//class ImageCompressWebpImagesTable extends Entity\DataManager
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
            (new Entity\IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

//            (new Entity\StringField('SITE_ID'))
//                ->configureRequired(),

            (new Entity\StringField('IMAGE_PATH'))
                ->configureRequired(),
//                ->configureUnique(),

            (new Entity\StringField('ORIGINAL_IMAGE_HASH'))
                ->configureRequired(),

            (new Entity\EnumField('IMAGE_TYPE'))
                ->configureValues([
                    Convert::TYPE_WEBP,
                    Convert::TYPE_AVIF,
                ])
                ->configureRequired(),

//            (new Entity\BooleanField('IMAGE_PROCESSED'))
//                ->configureValues('N', 'Y')
//                ->configureDefaultValue('N'),

//            (new Entity\DatetimeField('DATE_CREATE'))
//                ->configureDefaultValue(new DateTime),

            //            (new Entity\DatetimeField('DATE_CHECK'))
            //                ->configureNullable(),
            //                ->configureDefaultValue(new Main\DB\SqlExpression("NOW()")),

//            (new Entity\DatetimeField('DATE_UPDATE'))
//                ->configureNullable(),

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