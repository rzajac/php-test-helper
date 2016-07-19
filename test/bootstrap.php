<?php

use Kicaj\Tools\Db\DbConnector;
use Kicaj\Tools\Helper\Arr;

ini_set('display_errors', true);
error_reporting(E_ALL & ~E_STRICT);

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
    $timezone = Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_TIMEZONE', '');

    return [
        DbConnector::DB_CFG_DRIVER   => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_DRIVER', ''),
        DbConnector::DB_CFG_HOST     => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_HOST', ''),
        DbConnector::DB_CFG_USERNAME => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_USERNAME', ''),
        DbConnector::DB_CFG_PASSWORD => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_PASSWORD', ''),
        DbConnector::DB_CFG_DATABASE => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_DATABASE', ''),
        DbConnector::DB_CFG_PORT     => Arr::get($GLOBALS, 'TEST_DB_' . $dbName . '_PORT', 3306),
        DbConnector::DB_CFG_CONNECT  => true,
        DbConnector::DB_CFG_TIMEZONE => $timezone,
        DbConnector::DB_CFG_DEBUG    => true,
    ];
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
