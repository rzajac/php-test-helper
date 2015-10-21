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

use Kicaj\Test\Helper\Database\TestDb;
use Kicaj\Test\Helper\Database\DbGet;

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
     * @var TestDb
     */
    protected static $db;

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

    /**
     * Things that need to be done before each TestCase.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Connect to database
        if (self::$db === null) {
            $dbConfig = [
                'host' => $GLOBALS['DB_HOST'],
                'username' => $GLOBALS['DB_USERNAME'],
                'password' => $GLOBALS['DB_PASSWORD'],
                'database' => $GLOBALS['DB_DATABASE'],
                'port' => $GLOBALS['DB_PORT'],
                'driver' => $GLOBALS['DB_DRIVER'],
            ];

            self::$db = DbGet::factory($dbConfig);
            self::$db->dbConnect();

            // Setup fixture loader with database
            self::$fixtureLoader->setDb(self::$db);
        }

        // Load resident fixtures
        self::$db->truncateDbTables(static::$residentFixtures);
        self::loadSQLFixtures(static::$residentFixtures);
    }

    /**
     * Things to be done before each test case.
     */
    public function setUp()
    {
        // Drop all tables from fixtures
        self::$db->dropDbTables($this->fixtures);
        self::loadSQLFixtures($this->fixtures);
    }

    /**
     * Loads database SQL fixtures.
     *
     * @param array|string $fixtureNames
     */
    public static function loadSQLFixtures($fixtureNames)
    {
        if (is_string($fixtureNames)) {
            $fixtureNames = [$fixtureNames];
        }

        self::$fixtureLoader->loadFixtures($fixtureNames);
    }
}
