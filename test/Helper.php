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
namespace Kicaj\Test\TestHelperTest;

use Kicaj\Tools\Helper\Fn;
use mysqli;

/**
 * Helper with its own database interface implementation.
 *
 * We need it to independently check our database implementation.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class Helper
{
    /**
     * Database driver.
     *
     * @var mysqli
     */
    protected $driver;

    /**
     * Singleton.
     *
     * @var Helper
     */
    private static $instance;

    private function __construct()
    {
        $dbConfig = self::dbGetConfig();

        $this->driver = new mysqli(
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $dbConfig['port']);
    }

    public static function make()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns global test database configuration.
     *
     * @return array
     */
    public static function dbGetConfig()
    {
        $timezone = isset($GLOBALS['TEST_DB_TIMEZONE']) ? $GLOBALS['TEST_DB_TIMEZONE'] : '';
        return [
            'driver' => $GLOBALS['TEST_DB_DRIVER'],
            'host' => $GLOBALS['TEST_DB_HOST'],
            'username' => $GLOBALS['TEST_DB_USERNAME'],
            'password' => $GLOBALS['TEST_DB_PASSWORD'],
            'database' => $GLOBALS['TEST_DB_DATABASE'],
            'port' => $GLOBALS['TEST_DB_PORT'],
            'connect' => true,
            'timezone' => $timezone,
            'debug' => true
        ];
    }

    /**
     * Get database table names.
     *
     * @return string[]
     */
    public function dbGetTableNames()
    {
        $databaseName = self::dbGetConfig()['database'];

        $resp = $this->driver->query('SHOW TABLES');

        $tableNames = [];
        while ($row = $resp->fetch_assoc()) {
            $tableNames[] = $row['Tables_in_'.$databaseName];
        }

        return $tableNames;
    }

    /**
     * Return number of tables in database.
     *
     * @return int
     */
    public function dbGetTableCount()
    {
        return count($this->dbGetTableNames());
    }

    /**
     * Get database table data.
     *
     * @param string $tableName The database table name
     *
     * @return array
     */
    public function dbGetTableData($tableName)
    {
        $resp = $this->driver->query('SELECT * FROM '.$tableName);

        $data = [];
        while ($row = $resp->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get number of rows in the given database table.
     *
     * @param string $tableName The database table name
     *
     * @return int
     */
    public function dbGetTableRowCount($tableName)
    {
        $resp = $this->driver->query('SELECT COUNT(1) AS c FROM '.$tableName);
        if ($resp === false) {
            return -1;
        }

        return (int) $resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    /**
     * Disconnect from database.
     *
     * @return bool
     */
    public function dbClose()
    {
        return @$this->driver->close();
    }

    /**
     * Reset test database to known state.
     *
     * @return Helper
     */
    public function dbResetTestDbatabase()
    {
        $this->driver->query('DROP TABLE IF EXISTS test1');
        $this->driver->query('DROP TABLE IF EXISTS test2');

        // Create tables
        $this->driver->query('CREATE TABLE `test1` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col1` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');
        $this->driver->query('CREATE TABLE `test2` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `col2` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB');

        return $this;
    }

    /**
     * Load test data to database.
     *
     * @return Helper
     */
    public function dbLoadTestData()
    {
        $this->driver->query("INSERT INTO `test1` (`id`, `col1`) VALUES (NULL, '1')");
        $this->driver->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '2')");
        $this->driver->query("INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '22')");

        return $this;
    }

    /**
     * Truncate test database tables.
     *
     * @return Helper
     */
    public function dbTruncateTestTables()
    {
        $this->driver->query('TRUNCATE `test1`');
        $this->driver->query('TRUNCATE `test2`');

        return $this;
    }

    /**
     * Drop database table by name.
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function dbDropTable($tableName)
    {
        return (bool) $this->driver->query("DROP TABLE $tableName");
    }

    /**
     * Drop database tables.
     *
     * @param array $tableNames
     *
     * @return bool
     */
    public function dbDropTables(array $tableNames)
    {
        $ret = false;

        foreach ($tableNames as $tableName) {
            $result = $this->dbDropTable($tableName);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }

    /**
     * Drop all database tables.
     *
     * @return bool
     */
    public function dbDropAllTables()
    {
        $tableNames = $this->dbGetTableNames();

        return $this->dbDropTables($tableNames);
    }
}
