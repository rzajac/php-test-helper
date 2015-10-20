<?php

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

use mysqli;

/**
 * Base unit test class.
 *
 * @author             Rafal Zajac <rzajac@gmail.com>
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Database driver.
     *
     * @var mysqli
     */
    protected static $coreMySQL;

    /**
     * Database configuration.
     *
     * @var array
     */
    protected static $defDbConfig;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$defDbConfig = [
            'driver' => $GLOBALS['DB_DRIVER'],
            'username' => $GLOBALS['DB_USERNAME'],
            'password' => $GLOBALS['DB_PASSWORD'],
            'host' => $GLOBALS['DB_HOST'],
            'port' => $GLOBALS['DB_PORT'],
            'database' => $GLOBALS['DB_DATABASE']
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        static::$defDbConfig = [
            'driver' => $GLOBALS['DB_DRIVER'],
            'username' => $GLOBALS['DB_USERNAME'],
            'password' => $GLOBALS['DB_PASSWORD'],
            'host' => $GLOBALS['DB_HOST'],
            'port' => $GLOBALS['DB_PORT'],
            'database' => $GLOBALS['DB_DATABASE']
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();

        static::disconnectFromDb();
        static::$coreMySQL = null;
    }

    /**
     * Get database table names.
     *
     * @return string[]
     */
    protected static function getTableNames()
    {
        $resp = static::$coreMySQL->query('SHOW TABLES');

        $tableNames = [];
        while ($row = $resp->fetch_assoc()) {
            $tableNames[] = $row['Tables_in_' . static::$defDbConfig['database']];
        }

        return $tableNames;
    }

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name
     *
     * @return int
     */
    public static function getTableRowCount($tableName)
    {
        $resp = static::$coreMySQL->query('SELECT COUNT(1) AS c FROM '.$tableName);
        if ($resp === false) {
            //var_dump(static::$coreMySQL->error);
            return -1;
        }

        return (int) $resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    /**
     * Connect to database.
     */
    public static function connectToDb()
    {
        static::$coreMySQL = new mysqli(
            static::$defDbConfig['host'],
            static::$defDbConfig['username'],
            static::$defDbConfig['password'],
            static::$defDbConfig['database']);
    }

    /**
     * Disconnect from database.
     *
     * @return bool
     */
    public static function disconnectFromDb()
    {
        if (static::$coreMySQL) {
            return @static::$coreMySQL->close();
        } else {
            return true;
        }
    }

    /**
     * Reset test database to known state.
     */
    public static function resetTestDb()
    {
        static::$coreMySQL->query('DROP TABLE IF EXISTS test1');
        static::$coreMySQL->query('DROP TABLE IF EXISTS test2');

        // Create tables
        static::$coreMySQL->query('CREATE TABLE `test1` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col1` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        static::$coreMySQL->query('CREATE TABLE `test2` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');

        // Insert data
        static::$coreMySQL->query("INSERT INTO `test1` (`id`, `col1`) VALUES (NULL, '7')");

        static::$coreMySQL->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '8')");
        static::$coreMySQL->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '9')");
    }

    /**
     * Truncate test database tables.
     */
    public static function truncateTestTables()
    {
        static::$coreMySQL->query("TRUNCATE `test1`");
        static::$coreMySQL->query("TRUNCATE `test2`");
    }
}

