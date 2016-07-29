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
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\DbKit\DatabaseException;
use Kicaj\DbKit\DbConnect;

/**
 * Database test case.
 *
 * It manages database and fixtures.
 *
 * All methods are static so they can be called from setUpBeforeClass or tearDownAfterClass.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
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
    public static function dbGetConfig($testDbName, $connect = true, $debug = true)
    {
        $timezone = isset($GLOBALS['TEST_DB_' . $testDbName . '_TIMEZONE']) ? $GLOBALS['TEST_DB_' . $testDbName . '_TIMEZONE'] : '';

        return DbConnect::getCfg(
            $GLOBALS['TEST_DB_' . $testDbName . '_DRIVER'],
            $GLOBALS['TEST_DB_' . $testDbName . '_HOST'],
            $GLOBALS['TEST_DB_' . $testDbName . '_USERNAME'],
            $GLOBALS['TEST_DB_' . $testDbName . '_PASSWORD'],
            $GLOBALS['TEST_DB_' . $testDbName . '_DATABASE'],
            $GLOBALS['TEST_DB_' . $testDbName . '_PORT'],
            $connect,
            $timezone,
            $debug
        );
    }

    /**
     * Get database helper.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseException
     *
     * @return DbItf
     */
    public static function dbGetHelper($testDbName)
    {
        return DbGet::factory(self::dbGetConfig($testDbName));
    }

    /**
     * Get database fixture loader.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseException
     *
     * @return FixtureLoader
     */
    public static function dbGetFixtureLoader($testDbName = '')
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
     * @throws DatabaseException
     */
    public static function dbLoadFixtures($testDbName, $fixturePaths)
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
     * @throws DatabaseException
     */
    public static function dbDropTables($testDbName, $tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        self::dbGetHelper($testDbName)->dbDropTables($tableNames);
    }

    /**
     * Drop all tables from given test database.
     *
     * @param string $testDbName The name of database connection details form phpunit.xml.
     *
     * @throws DatabaseException
     */
    public static function dbDropAllTables($testDbName)
    {
        $db = self::dbGetHelper($testDbName);

        $db->dbDropTables($db->dbGetTableNames());
    }
}
