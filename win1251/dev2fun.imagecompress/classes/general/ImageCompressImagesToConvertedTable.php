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

class ImageCompressImagesToConvertedTable extends Entity\DataManager
{
    static $module_id = "dev2fun.imagecompress";

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
//        return 'b_d2f_imagecompress_convert_images';
        return 'b_d2f_imagecompress_images_to_converted';
    }

    public static function getTableTitle()
    {
        return Loc::getMessage('DEV2FUN_IMAGECOMPRESS_REFERENCE_TABLE_TITLE');
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
//                ->configurePrimary()
//                ->configureAutocomplete(),

            new Entity\IntegerField('IMAGE_ID', [
                'required' => true,
            ]),
//                ->configureRequired(),

            (new Entity\ReferenceField(
                'IMAGE',
                ImageCompressImagesTable::class,
                [
                    '=this.IMAGE_ID' => 'ref.ID'
                ],
//                ['join_type' => 'INNER']
            )),

            new Entity\IntegerField('CONVERTED_IMAGE_ID', [
                'required' => true,
            ]),
//                ->configureRequired(),

            (new Entity\ReferenceField(
                'CONVERTED_IMAGE',
                ImageCompressImagesConvertedTable::class,
                [
                    '=this.CONVERTED_IMAGE_ID' => 'ref.ID',
                    '=this.IMAGE_TYPE' => 'ref.IMAGE_TYPE',
                ],
//                ['join_type' => 'INNER']
            )),

            new Entity\EnumField('IMAGE_TYPE', [
                'values' => [
                    Convert::TYPE_WEBP,
                    Convert::TYPE_AVIF,
                ],
                'required' => true,
            ]),
//                ->configureValues([
//                    Convert::TYPE_WEBP,
//                    Convert::TYPE_AVIF,
//                ])
//                ->configureRequired(),

            new Entity\BooleanField('IMAGE_PROCESSED', [
                'values' => ['N', 'Y'],
                'default_value' => 'N',
            ]),
//                ->configureValues('N', 'Y')
//                ->configureDefaultValue('N'),

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
            'unique_image_id_converted_image_id',
            ['IMAGE_ID', 'CONVERTED_IMAGE_ID'],
            $connection::INDEX_UNIQUE
        );

//        $connection->createIndex(
//            static::getTableName(),
//            'indx_image_type',
//            ['IMAGE_TYPE']
//        );

        $connection->createIndex(
            static::getTableName(),
            'indx_image_processed',
            ['IMAGE_PROCESSED']
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