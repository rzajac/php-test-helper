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

namespace Kicaj\Test\Helper\TestCase;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\Helper\Loader\FixtureLoaderEx;

/**
 * Database test case.
 *
 * It manages database and fixtures.
 *
 * All methods are static so they can be called from setUpBeforeClass or tearDownAfterClass.
 */
abstract class DbTestCase extends FixtureTestCase
{
    /**
     * Returns database configuration.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     * @param bool   $connect    Set to true to connect to database right away.
     * @param bool   $debug      Set to true to put database driver in debug mode.
     *
     * @return array
     */
    public static function dbGetConfig(string $testDbName, bool $connect = true, bool $debug = true)
    {
        $timezone = isset($GLOBALS['TEST_DB_' . $testDbName . '_TIMEZONE']) ? $GLOBALS['TEST_DB_' . $testDbName . '_TIMEZONE'] : '';

        if ($timezone == '') {
            $timezone = 'UTC';
        }

        return [
            DbItf::DB_CFG_DRIVER => $GLOBALS['TEST_DB_' . $testDbName . '_DRIVER'],
            DbItf::DB_CFG_HOST => $GLOBALS['TEST_DB_' . $testDbName . '_HOST'],
            DbItf::DB_CFG_USERNAME => $GLOBALS['TEST_DB_' . $testDbName . '_USERNAME'],
            DbItf::DB_CFG_PASSWORD => $GLOBALS['TEST_DB_' . $testDbName . '_PASSWORD'],
            DbItf::DB_CFG_DATABASE => $GLOBALS['TEST_DB_' . $testDbName . '_DATABASE'],
            DbItf::DB_CFG_PORT => (int)$GLOBALS['TEST_DB_' . $testDbName . '_PORT'],
            DbItf::DB_CFG_CONNECT => $connect,
            DbItf::DB_CFG_TIMEZONE => $timezone,
            DbItf::DB_CFG_DEBUG => $debug
        ];
    }

    /**
     * Get database helper.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseEx
     *
     * @return DbItf
     */
    public static function dbGetHelper(string $testDbName): DbItf
    {
        return DbGet::factory(self::dbGetConfig($testDbName));
    }

    /**
     * Get database fixture loader.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseEx
     *
     * @return FixtureLoader
     */
    public static function dbGetFixtureLoader(string $testDbName = ''): FixtureLoader
    {
        $db = $testDbName ? self::dbGetHelper($testDbName) : null;

        return parent::getFixtureLoader($db);
    }

    /**
     * Load fixtures to database.
     *
     * @param string          $testDbName   The name of database connection details form phpunit.xml.
     * @param string|string[] $fixturePaths The array of fixture paths to load to database.
     *
     * @throws DatabaseEx
     * @throws FixtureLoaderEx
     */
    public static function dbLoadFixtures(string $testDbName, $fixturePaths)
    {
        if (is_string($fixturePaths)) {
            $fixturePaths = [$fixturePaths];
        }

        $fLoader = self::dbGetFixtureLoader($testDbName);
        $fLoader->loadDbFixtures($fixturePaths);
    }

    /**
     * Drop table or tables from given test database.
     *
     * @param string          $testDbName The name of database connection details form phpunit.xml.
     * @param string|string[] $tableNames The table or tables to drop from the database.
     *
     * @throws DatabaseEx
     */
    public static function dbDropTables(string $testDbName, $tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        self::dbGetHelper($testDbName)->dbDropTables($tableNames);
    }

    /**
     * Drop view or views from given test database.
     *
     * @param string          $testDbName The name of database connection details form phpunit.xml.
     * @param string|string[] $viewNames  The view or views to drop from the database.
     *
     * @throws DatabaseEx
     */
    public static function dbDropViews(string $testDbName, $viewNames)
    {
        if (is_string($viewNames)) {
            $viewNames = [$viewNames];
        }

        self::dbGetHelper($testDbName)->dbDropViews($viewNames);
    }

    /**
     * Drop all tables from given test database.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseEx
     */
    public static function dbDropAllTables(string $testDbName)
    {
        $db = self::dbGetHelper($testDbName);

        $db->dbDropTables($db->dbGetTableNames());
    }

    /**
     * Drop all views from given test database.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseEx
     */
    public static function dbDropAllViews(string $testDbName)
    {
        $db = self::dbGetHelper($testDbName);

        $db->dbDropViews($db->dbGetViewNames());
    }

    /**
     * Check table exists.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     * @param string $tableName  The table name to check.
     *
     * @throws DatabaseEx
     *
     * @return bool
     */
    public static function dbTableExists(string $testDbName, string $tableName): bool
    {
        $tableNames = self::dbGetHelper($testDbName)->dbGetTableNames();

        return in_array($tableName, $tableNames);
    }

    /**
     * Check view exists.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     * @param string $viewName   The view name to check.
     *
     * @throws DatabaseEx
     *
     * @return bool
     */
    public static function dbViewExists(string $testDbName, string $viewName): bool
    {
        $viewNames = self::dbGetHelper($testDbName)->dbGetViewNames();

        return in_array($viewName, $viewNames);
    }
}
