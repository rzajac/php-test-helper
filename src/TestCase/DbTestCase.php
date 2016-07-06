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
            self::$db = DbGet::factory(self::dbGetConfig());

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
     * @return array
     */
    public static function dbGetConfig()
    {
        $timezone = isset($GLOBALS['TEST_DB_TIMEZONE']) ? $GLOBALS['TEST_DB_TIMEZONE'] : '';

        return DbConnect::getCfg(
            $GLOBALS['TEST_DB_DRIVER'],
            $GLOBALS['TEST_DB_HOST'],
            $GLOBALS['TEST_DB_USERNAME'],
            $GLOBALS['TEST_DB_PASSWORD'],
            $GLOBALS['TEST_DB_DATABASE'],
            $GLOBALS['TEST_DB_PORT'],
            true,
            $timezone,
            true
        );
    }

    /**
     * Drop database table.
     *
     * @param string $tableName The database table name
     *
     * @return bool true on success
     */
    public static function dbDropTable($tableName)
    {
        return self::$db->dbDropTable($tableName);
    }

    /**
     * Drop database tables.
     *
     * @param string[] $tableNames The database table name
     *
     * @return bool true if all tables were dropped
     */
    public static function dbDropTables(array $tableNames)
    {
        $ret = true;
        foreach ($tableNames as $tableName) {
            $result = self::dbDropTable($tableName);
            $ret    = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
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
     * Truncate database table.
     *
     * @param string $tableName The database table name
     *
     * @return bool true on success
     */
    public static function dbTruncateTable($tableName)
    {
        return self::$db->dbTruncateTable($tableName);
    }

    /**
     * Truncate database tables.
     *
     * @param string[] $tableNames The database table name
     *
     * @return bool true if all tables were dropped
     */
    public static function dbTruncateTables(array $tableNames)
    {
        $ret = true;
        foreach ($tableNames as $tableName) {
            $result = self::dbTruncateTable($tableName);
            $ret    = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
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
}
