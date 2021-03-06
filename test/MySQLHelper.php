<?php declare(strict_types=1);

/**
 * Copyright 2015 Rafal Zajac <rzajac@gmail.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace Kicaj\Test\TestHelperTest;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbItf;

class MySQLHelper
{
    /**
     * Reset MySQL databases.
     *
     * @throws DatabaseEx
     */
    public static function resetMySQLDatabases()
    {
        $dbConfig1 = getUnitTestDbConfig('HELPER1');
        $mysql1 = new \mysqli(
            $dbConfig1[DbItf::DB_CFG_HOST],
            $dbConfig1[DbItf::DB_CFG_USERNAME],
            $dbConfig1[DbItf::DB_CFG_PASSWORD],
            $dbConfig1[DbItf::DB_CFG_DATABASE],
            $dbConfig1[DbItf::DB_CFG_PORT]);

        $dbConfig2 = getUnitTestDbConfig('HELPER2');
        $mysql2 = new \mysqli(
            $dbConfig2[DbItf::DB_CFG_HOST],
            $dbConfig2[DbItf::DB_CFG_USERNAME],
            $dbConfig2[DbItf::DB_CFG_PASSWORD],
            $dbConfig2[DbItf::DB_CFG_DATABASE],
            $dbConfig2[DbItf::DB_CFG_PORT]);

        // Drop all tables from testHelper1.
        self::dropAllMysqlTables($mysql1, $dbConfig1[DbItf::DB_CFG_DATABASE]);

        // Create tables in testHelper1 database.
        $mysql1->query('CREATE TABLE `test1` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col1` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        $mysql1->query('CREATE TABLE `test2` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        $mysql1->query('CREATE TABLE `test3` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        $mysql1->query('CREATE VIEW `my_view`AS SELECT * FROM `test1`;');

        // Insert into testHelper1 database.
        $mysql1->query("INSERT INTO `test1` (`id`, `col1`) VALUES (NULL, '1')");
        $mysql1->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '2')");
        $mysql1->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '22')");

        // Drop all tables from testHelper2.
        self::dropAllMysqlTables($mysql2, $dbConfig2[DbItf::DB_CFG_DATABASE]);

        // Create tables in testHelper2 database.
        $mysql2->query('CREATE TABLE `test2` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        $mysql2->query('CREATE TABLE `test3` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');

        // Insert into testHelper1 database.
        $mysql2->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '123')");
        $mysql2->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '321')");
    }

    /**
     * Get database table names.
     *
     * @param \mysqli $mysql  The initialized MySQL connection.
     * @param string  $dbName The database name.
     *
     * @throws DatabaseEx
     *
     * @return \string[]
     */
    protected static function getMysqlTableNames($mysql, $dbName)
    {
        $sql = sprintf('SHOW TABLES FROM `%s`', $dbName);
        $resp = $mysql->query($sql);
        if (!$resp) {
            throw new DatabaseEx($mysql->error);
        }

        $tableNames = [];
        while ($row = $resp->fetch_assoc()) {
            foreach ($row as $tableName) {
                $tableNames[] = $tableName;
            }
        }

        return $tableNames;
    }

    /**
     * Drop database tables.
     *
     * @param \mysqli         $mysql      The initialized MySQL connection.
     * @param string|string[] $tableNames The table or table names to drop.
     *
     * @throws DatabaseEx
     */
    protected static function dropMysqlTables($mysql, $tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        foreach ($tableNames as $tableName) {
            $result = (bool)$mysql->query("DROP TABLE $tableName");
            if (!$result) {
                $result = (bool)$mysql->query("DROP VIEW $tableName");
                if (!$result) {
                    throw new DatabaseEx($mysql->error);
                }
            }
        }
    }

    /**
     * Drop all database tables.
     *
     * @param \mysqli $mysql  The initialized MySQL connection.
     * @param string  $dbName The database name.
     *
     * @throws DatabaseEx
     */
    protected static function dropAllMysqlTables($mysql, $dbName)
    {
        $tableNames = self::getMysqlTableNames($mysql, $dbName);
        self::dropMysqlTables($mysql, $tableNames);
    }
}
