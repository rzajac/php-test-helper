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
namespace Kicaj\Test\Helper\Database\Driver;

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Tools\Exception;
use Kicaj\Tools\Helper\Fn;
use Kicaj\Tools\Traits\Error;

/**
 * Class MySQL.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class MySQL implements DbItf
{
    use Error;

    /**
     * The MySQL class.
     *
     * @var \mysqli
     */
    protected $mysql;

    /**
     * Database configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Set to true when connected to database.
     *
     * @var bool
     */
    protected $isConnected = false;

    /**
     * Configure database.
     *
     * @param array $config The database configuration
     *
     * @return MySQL
     */
    public function dbSetup(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Connect to database.
     *
     * @return bool Returns true on success.
     */
    public function dbConnect()
    {
        try {
            $this->mysql = new \mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database'],
                $this->config['port']);
            $this->isConnected = true;
        } catch (\Exception $e) {
            $this->isConnected = false;
            return $this->addError($e);
        }

        return true;
    }

    /**
     * Drop table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function dbDropTable($tableName)
    {
        $sql = sprintf('DROP TABLE `%s`', $tableName);
        return (bool) $this->mysql->query($sql);
    }

    /**
     * Drop list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * @return bool Returns true on success, false if one or more operations failed
     */
    public function dbDropTables(array $tableNames)
    {
        $ret = true;
        foreach ($tableNames as $tableName) {
            $result = $this->dbDropTable($tableName);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }

    /**
     * Truncate table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function dbTruncateTable($tableName)
    {
        $sql = sprintf('TRUNCATE TABLE `%s`', $tableName);
        return (bool) $this->mysql->query($sql);
    }

    /**
     * Truncate list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * @return bool Returns true on success, false if one or more operations failed
     */
    public function dbTruncateTables(array $tableNames)
    {
        $ret = true;

        foreach ($tableNames as $tableName) {
            $result = $this->dbTruncateTable($tableName);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name
     *
     * @return int Returns -1 on error
     */
    public function dbCountTableRows($tableName)
    {
        $sql = sprintf('SELECT COUNT(1) AS c FROM `%s`', $tableName);
        $resp = $this->mysql->query($sql);
        if ($resp === false) {
            $this->addError($this->mysql->error);

            return -1;
        }

        return (int) $resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    /**
     * Returns list of database tables.
     *
     * @return string[]
     */
    public function dbGetTableNames()
    {
        $sql = sprintf('SHOW TABLES FROM `%s`', $this->config['database']);
        $resp = $this->mysql->query($sql);

        $tableNames = [];
        while ($row = $resp->fetch_assoc()) {
            foreach ($row as $tableName) {
                $tableNames[] = $tableName;
            }
        }

        return $tableNames;
    }

    /**
     * Run database query.
     *
     * @param mixed $query
     *
     * @throws Exception
     *
     * @return bool|\mysqli_result
     */
    public function dbRunQuery($query)
    {
        $queries = is_array($query) ? $query : [$query];

        $resp = false;
        foreach ($queries as $sql) {
            $resp = $this->mysql->query($sql);
            if (!$resp) {
                throw new Exception($this->mysql->error);
            }
        }

        return $resp;
    }

    /**
     * Returns true if connected to database.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    /**
     * Close database connection.
     *
     * @return bool
     */
    public function dbClose()
    {
        $this->isConnected = false;
        return $this->mysql->close();
    }
}
