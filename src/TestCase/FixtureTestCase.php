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

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\Helper\Loader\FixtureLoaderEx;

/**
 * Test case with fixtures.
 *
 * It manages fixtures.
 */
abstract class FixtureTestCase extends TestCase
{
    /**
     * Returns fixtures root directory path.
     *
     * @return string
     */
    public static function getFixturesRootPath(): string
    {
        return $GLOBALS['TEST_FIXTURE_DIRECTORY'];
    }

    /**
     * Get fixture loader.
     *
     * @param DbItf $dbDrv The database to load fixtures to.
     *
     * @return FixtureLoader
     */
    public static function getFixtureLoader(DbItf $dbDrv = null): FixtureLoader
    {
        return new FixtureLoader(self::getFixturesRootPath(), $dbDrv);
    }

    /**
     * Return content of the given fixture file.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @throws FixtureLoaderEx
     *
     * @return mixed
     */
    public static function getFixtureData(string $fixturePath)
    {
        return self::getFixtureLoader()->getFixtureData($fixturePath);
    }

    /**
     * Return raw content of the given fixture file.
     *
     * @param string $fixturePath The fixture path relative to fixturesRootPath.
     *
     * @return string
     */
    public static function getFixtureRawData(string $fixturePath): string
    {
        return self::getFixtureLoader()->getFixtureRawData($fixturePath);
    }
}
