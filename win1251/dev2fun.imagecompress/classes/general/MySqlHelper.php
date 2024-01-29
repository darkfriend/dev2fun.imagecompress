<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.8.0
 */

namespace Dev2fun\ImageCompress;

class MySqlHelper
{
    /**
     * @param string $tableName
     * @param array $insertRowsFields
     * @return string
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public static function getInsertIgnore(string $tableName, array $insertFields): string
    {
        $connection = \Bitrix\Main\Application::getInstance()->getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $insert = $sqlHelper->prepareInsert($tableName, $insertFields);

        if ($tableName && !empty($insert[0]) && !empty($insert[1])) {
            $sql = "
				INSERT IGNORE INTO {$sqlHelper->quote($tableName)} ({$insert[0]})
				VALUES ({$insert[1]})";
        } else {
            $sql = '';
        }

        return $sql;
    }

    /**
     * @param string $tableName
     * @param array $insertRowsFields
     * @return string
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public static function getInsertIgnoreMulti(string $tableName, array $insertRowsFields): string
    {
        $connection = \Bitrix\Main\Application::getInstance()->getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $columns = null;
        $insertRows = [];
        foreach ($insertRowsFields as $insertFields) {
            $insert = $sqlHelper->prepareInsert($tableName, $insertFields);
            if (empty($columns) && !empty($insert[0])) {
                $columns = $insert[0];
            }
            if (!empty($insert[1])) {
                $insertRows[] = "({$insert[1]})";
            }
        }

        if ($columns && $insertRows && $tableName) {
            $sql = "
				INSERT IGNORE INTO {$sqlHelper->quote($tableName)} ({$columns})
				VALUES ".implode(', ', $insertRows);
        } else {
            $sql = '';
        }

        return $sql;
    }
}