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

class ImageCompressImagesTable extends Entity\DataManager
{
    static $module_id = "dev2fun.imagecompress";

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_d2f_imagecompress_images';
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
                'unique' => true,
            ]),

            new Entity\StringField('IMAGE_HASH', [
                'nullable' => true,
            ]),

            new Entity\BooleanField('IMAGE_IGNORE', [
                'values' => ['N', 'Y'],
                'default_value' => 'N',
            ]),
//                ->configureValues('N', 'Y')
//                ->configureDefaultValue('N'),

            new Entity\DatetimeField('DATE_CHECK', [
                'nullable' => true,
            ]),
//                ->configureNullable(),

            new Entity\DatetimeField('DATE_UPDATE', [
                'nullable' => true,
            ]),
//                ->configureNullable(),

            (new Entity\ReferenceField(
                'CONVERTED_IMAGE',
                ImageCompressImagesToConvertedTable::class,
                [
                    '=this.ID' => 'ref.IMAGE_ID',
//                    '=this.IMAGE_TYPE' => 'ref.IMAGE_TYPE',
                ]
//                ['join_type' => 'INNER']
            )),

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
            'indx_date_check',
            ['DATE_CHECK']
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