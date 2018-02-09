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

use Kicaj\Test\Helper\Database\DbItf;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Returns database configuration defined in phpunit.xml.
 *
 * @param string $dbName The database configuration name.
 *
 * @return array
 */
function getUnitTestDbConfig($dbName)
{
    $timezone = ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_TIMEZONE', '');

    return [
        DbItf::DB_CFG_DRIVER => ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_DRIVER', ''),
        DbItf::DB_CFG_HOST => ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_HOST', ''),
        DbItf::DB_CFG_USERNAME => ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_USERNAME', ''),
        DbItf::DB_CFG_PASSWORD => ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_PASSWORD', ''),
        DbItf::DB_CFG_DATABASE => ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_DATABASE', ''),
        DbItf::DB_CFG_PORT => (int)ArrGet($GLOBALS, 'TEST_DB_' . $dbName . '_PORT', 3306),
        DbItf::DB_CFG_CONNECT => true,
        DbItf::DB_CFG_TIMEZONE => $timezone,
        DbItf::DB_CFG_DEBUG => true,
    ];
}

/**
 * Return array key value or default if it does not exist.
 *
 * @param array  $array   The array
 * @param string $key     The key to get value for
 * @param mixed  $default The default value to return if key doesn't exist
 *
 * @return mixed
 */
function ArrGet($array, $key, $default = null)
{
    if (!is_array($array)) {
        return $default;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Returns fixtures root directory path.
 *
 * @return string
 */
function getFixturesRootPath()
{
    return $GLOBALS['TEST_FIXTURE_DIRECTORY'];
}
