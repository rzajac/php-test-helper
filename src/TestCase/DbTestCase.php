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

use Kicaj\Tools\Db\DbConnect;

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
     * Returns database configuration.
     *
     * @param string $testDbName The name of database configuration.
     * @param bool   $connect    Set to true to connect to database right away.
     * @param bool   $debug      Set to true to put database driver in debug mode.
     *
     * @return array
     */
    public static function dbGetConfig($testDbName, $connect = true, $debug = true)
    {
        $timezone = isset($GLOBALS['TEST_DB_'.$testDbName.'_TIMEZONE']) ? $GLOBALS['TEST_DB_'.$testDbName.'_TIMEZONE'] : '';

        return DbConnect::getCfg(
            $GLOBALS['TEST_DB_'.$testDbName.'_DRIVER'],
            $GLOBALS['TEST_DB_'.$testDbName.'_HOST'],
            $GLOBALS['TEST_DB_'.$testDbName.'_USERNAME'],
            $GLOBALS['TEST_DB_'.$testDbName.'_PASSWORD'],
            $GLOBALS['TEST_DB_'.$testDbName.'_DATABASE'],
            $GLOBALS['TEST_DB_'.$testDbName.'_PORT'],
            $connect,
            $timezone,
            $debug
        );
    }
}
