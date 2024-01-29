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
            (new Entity\IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

//            (new Entity\StringField('SITE_ID'))
//                ->configureRequired(),

            (new Entity\StringField('IMAGE_PATH'))
                ->configureUnique(),

            (new Entity\StringField('IMAGE_HASH'))
                ->configureNullable(),

            (new Entity\BooleanField('IMAGE_IGNORE'))
                ->configureValues('N', 'Y')
                ->configureDefaultValue('N'),

//            (new Entity\EnumField('IMAGE_TYPE'))
//                ->configureValues([
//                    'webp',
//                    'avif',
//                ])
//                ->configureRequired(),

//            (new Entity\DatetimeField('DATE_CREATE'))
//                ->configureDefaultValue(new Main\DB\SqlExpression("NOW()")),

            (new Entity\DatetimeField('DATE_CHECK'))
                ->configureNullable(),
//                ->configureDefaultValue(new Main\DB\SqlExpression("NOW()")),

            (new Entity\DatetimeField('DATE_UPDATE'))
                ->configureNullable(),

//            (new Entity\DatetimeField('DATE_CHECK'))
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