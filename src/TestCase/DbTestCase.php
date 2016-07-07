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
namespace Kicaj\Test\Helper\TestCase;

use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Tools\Db\DbConnect;
use Kicaj\Tools\Exception;
use Kicaj\Tools\Helper\Fn;

/**
 * Database test case.
 *
 * It manages database and fixtures.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
abstract class DbTestCase extends FixtureTestCase
{
    /**
     * Database driver.
     *
     * @var DbItf
     */
    private static $db;

    /**
     * Fixtures to load and tear down for each test.
     *
     * @var array
     */
    protected $fixtures = [];

    /**
     * Fixtures to load once per DbTestCase class.
     *
     * @var array
     */
    protected static $residentFixtures = [];

    // @codeCoverageIgnoreStart
    /**
     * Things that need to be done before each TestCase.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::setUpDb();

        // Load resident fixtures
        self::dbLoadFixtures(static::$residentFixtures);
    }

    /**
     * Setup test database connection.
     *
     * @throws Exception
     */
    protected static function setUpDb()
    {
        if (self::$db === null) {
            self::$db = DbGet::factory(self::dbGetConfig(DbItf::DB_NAME_DEFAULT));

            // Pass database interface to fixture loader.
            self::setFixtureDb(self::$db);
        }
    }
    // @codeCoverageIgnoreEnd

    /**
     * Things to be done before each test case.
     */
    public function setUp()
    {
        self::dbLoadFixtures($this->fixtures);
    }

    /**
     * Returns database configuration.
     *
     * @param string $testDbName The name of database configuration.
     *
     * @return array
     */
    public static function dbGetConfig($testDbName)
    {
        $timezone = isset($GLOBALS['TEST_DB_'.$testDbName.'_TIMEZONE']) ? $GLOBALS['TEST_DB_'.$testDbName.'_TIMEZONE'] : '';

        return DbConnect::getCfg(
            $GLOBALS['TEST_DB_'.$testDbName.'_DRIVER'],
            $GLOBALS['TEST_DB_'.$testDbName.'_HOST'],
            $GLOBALS['TEST_DB_'.$testDbName.'_USERNAME'],
            $GLOBALS['TEST_DB_'.$testDbName.'_PASSWORD'],
            $GLOBALS['TEST_DB_'.$testDbName.'_DATABASE'],
            $GLOBALS['TEST_DB_'.$testDbName.'_PORT'],
            true,
            $timezone,
            true
        );
    }

    /**
     * Drop database table or tables.
     *
     * @param string|string[] $tableNames The database table name or names.
     *
     * @return bool true if all tables were dropped
     */
    public static function dbDropTables($tableNames)
    {
        return self::$db->dbDropTables($tableNames);
    }

    /**
     * Drop all database tables.
     *
     * @return bool true if all tables were dropped
     */
    public static function dbDropAllTables()
    {
        $tableNames = self::dbGetTableNames();

        return self::dbDropTables($tableNames);
    }

    /**
     * Truncate database table or tables.
     *
     * @param string|string[] $tableNames The database table name or names to truncate.
     *
     * @return bool Returns true if all tables were dropped, false otherwise.
     */
    public static function dbTruncateTables(array $tableNames)
    {
        return self::$db->dbTruncateTables($tableNames);
    }

    /**
     * Return number of rows in the database table.
     *
     * @param string $tableName The database table name
     *
     * @return int
     */
    public static function dbCountTableRows($tableName)
    {
        return self::$db->dbCountTableRows($tableName);
    }

    /**
     * Return database table names.
     *
     * @return string[]
     */
    public static function dbGetTableNames()
    {
        return self::$db->dbGetTableNames();
    }

    /**
     * Check if table exists in the database.
     *
     * @param string $tableName The table name to check existence in the database.
     *
     * @return bool
     */
    public static function dbTableExists($tableName)
    {
        return in_array($tableName, self::dbGetTableNames());
    }
}
