<?php

// The project root folder
use Kicaj\Tools\Db\DbConnector;

define('PROJECT_PATH', realpath(__DIR__.'/..'));
define('FIXTURE_PATH', PROJECT_PATH.'/test/fixtures');

require_once PROJECT_PATH.'/vendor/autoload.php';

/**
 * Returns database configuration defined in phpunit.xml.
 *
 * @param string $dbName The database configuration name.
 *
 * @return array
 */
function getUnitTestDbConfig($dbName)
{
    $timezone = isset($GLOBALS['TEST_DB_'.$dbName.'_TIMEZONE']) ? $GLOBALS['TEST_DB_'.$dbName.'_TIMEZONE'] : '';

    return [
        DbConnector::DB_CFG_DRIVER => $GLOBALS['TEST_DB_'.$dbName.'_DRIVER'],
        DbConnector::DB_CFG_HOST => $GLOBALS['TEST_DB_'.$dbName.'_HOST'],
        DbConnector::DB_CFG_USERNAME => $GLOBALS['TEST_DB_'.$dbName.'_USERNAME'],
        DbConnector::DB_CFG_PASSWORD => $GLOBALS['TEST_DB_'.$dbName.'_PASSWORD'],
        DbConnector::DB_CFG_DATABASE => $GLOBALS['TEST_DB_'.$dbName.'_DATABASE'],
        DbConnector::DB_CFG_PORT => $GLOBALS['TEST_DB_'.$dbName.'_PORT'],
        DbConnector::DB_CFG_CONNECT => true,
        DbConnector::DB_CFG_TIMEZONE => $timezone,
        DbConnector::DB_CFG_DEBUG => true
    ];
}
