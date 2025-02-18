<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.11.0
 */

namespace Dev2fun\ImageCompress;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class ImageCompressImagesToConvertedTable extends Entity\DataManager
{
    static $module_id = "dev2fun.imagecompress";
    /**
     * @var null|string
     */
    protected static $engine = null;

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
                    '=this.IMAGE_ID' => 'ref.ID',
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
    public static function createTable(): void
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

        static::addForeignKey();
    }

    /**
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function addForeignKey(): void
    {
        $connection = static::getEntity()->getConnection();

        if (
            $connection->getType() !== 'mysql'
            || ($connection->getType() === 'mysql' &&  static::getEngine() === 'InnoDB')
        ) {
            $table = static::getTableName();
            $tableSourceImages = ImageCompressImagesTable::getTableName();
            $tableConvertedImages = ImageCompressImagesConvertedTable::getTableName();


            $connection->queryExecute("ALTER TABLE {$table} ADD CONSTRAINT FK_IMAGE_ID FOREIGN KEY (IMAGE_ID) REFERENCES {$tableSourceImages}(id) ON DELETE CASCADE");
            $connection->queryExecute("ALTER TABLE {$table} ADD CONSTRAINT FK_CONVERTED_IMAGE_ID FOREIGN KEY (CONVERTED_IMAGE_ID) REFERENCES {$tableConvertedImages}(id) ON DELETE CASCADE");
        }
    }

    /**
     * Drop table
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function dropTable(): void
    {
        if (static::getEntity()->getConnection()->getType() !== 'mysql') {
            static::dropForeignKey();
        }
        static::getEntity()->getConnection()->dropTable(static::getTableName());
    }

    /**
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function dropForeignKey(): void
    {
        $table = static::getTableName();

        static::getEntity()
            ->getConnection()
            ->queryExecute("ALTER TABLE {$table} DROP CONSTRAINT FK_IMAGE_ID");

        static::getEntity()
            ->getConnection()
            ->queryExecute("ALTER TABLE {$table} DROP CONSTRAINT FK_CONVERTED_IMAGE_ID");
    }

    /**
     * Возвращает движок для таблицы (например InnoDB)
     * @return string|null
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function getEngine(): ?string
    {
        if (static::$engine === null) {
            $table = static::getTableName();
            static::$engine = static::getEntity()
                ->getConnection()
                ->query("SHOW TABLE STATUS WHERE Name = '{$table}'")
                ->fetch()['Engine'] ?? '';
        }

        return static::$engine;
    }

    /**
     * Удаляет битые связи
     * @param int|null $limit
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function removeWrongRelations(?int $limit = null): void
    {
        $table = static::getTableName();
        $tableSourceImages = ImageCompressImagesTable::getTableName();
        $tableConvertedImages = ImageCompressImagesConvertedTable::getTableName();

        $limitStr = '';
        if ($limit) {
            $limitStr = "LIMIT {$limit}";
        }

        $rows = static::getEntity()
            ->getConnection()
            ->query(<<<SQL
                SELECT {$table}.ID
                FROM {$table}
                    LEFT JOIN {$tableSourceImages} ON {$tableSourceImages}.ID = {$table}.IMAGE_ID
                    LEFT JOIN {$tableConvertedImages} ON {$tableConvertedImages}.ID = {$table}.CONVERTED_IMAGE_ID
                WHERE {$tableConvertedImages}.ID IS NULL OR {$tableSourceImages}.ID IS NULL
                {$limitStr}
SQL
            )
            ->fetchAll();

        if (!$rows) {
            return;
        }

        $ids = implode(
            ',',
            array_column($rows, 'ID')
        );

        static::getEntity()->getConnection()
            ->queryExecute(
                "DELETE FROM {$table} WHERE ID IN ({$ids})"
            );
    }

    /**
     * Возвращает количество битых связей
     * @return int
     * @throws Main\ArgumentException
     * @throws Main\DB\SqlQueryException
     * @throws Main\SystemException
     */
    public static function getCountWrongRelations(): int
    {
        $table = static::getTableName();
        $tableSourceImages = ImageCompressImagesTable::getTableName();
        $tableConvertedImages = ImageCompressImagesConvertedTable::getTableName();

        return static::getEntity()
            ->getConnection()
            ->query(<<<SQL
                SELECT COUNT(*) as cnt
                FROM {$table}
                    LEFT JOIN {$tableSourceImages} ON {$tableSourceImages}.ID = {$table}.IMAGE_ID
                    LEFT JOIN {$tableConvertedImages} ON {$tableConvertedImages}.ID = {$table}.CONVERTED_IMAGE_ID
                WHERE {$tableConvertedImages}.ID IS NULL OR {$tableSourceImages}.ID IS NULL
SQL
            )
            ->fetch()['cnt'] ?? 0;
    }
}