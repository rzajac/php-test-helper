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

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Tools\Exception;

/**
 * Test case with fixtures.
 *
 * It manages fixtures.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
abstract class FixtureTestCase extends TestCase
{
    /**
     * Fixture loader.
     *
     * @var FixtureLoader
     */
    private static $fixtureLoader;

    // @codeCoverageIgnoreStart
    /**
     * Things that need to be done before each TestCase.
     */
    public static function setUpBeforeClass()
    {
        self::setUpLoader();
    }

    /**
     * Setup fixture loader
     */
    public static function setUpLoader()
    {
        // Setup fixture loader
        if (self::$fixtureLoader == null) {
            self::$fixtureLoader = new FixtureLoader(null, $GLOBALS['FIXTURE_DIRECTORY']);
        }
    }

    /**
     * Set database to load fixtures to.
     *
     * @param DbItf $db The database interface
     *
     * @throws Exception
     */
    protected static function setFixtureDb(DbItf $db)
    {
        self::setUpLoader();
        self::$fixtureLoader->setDb($db);
    }
    // @codeCoverageIgnoreEnd

    /**
     * Load collection of fixtures.
     *
     * @param array $fixtureNames The array of fixture file names
     *
     * @throws Exception
     */
    protected static function dbLoadFixtures(array $fixtureNames)
    {
        self::$fixtureLoader->dbLoadFixtures($fixtureNames);
    }

    /**
     * Load fixture from file.
     *
     * @param string $fixtureName The fixture file name
     *
     * @return mixed
     */
    protected static function loadFileFixture($fixtureName)
    {
        return self::$fixtureLoader->loadFileFixture($fixtureName);
    }
}
