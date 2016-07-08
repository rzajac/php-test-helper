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
use Kicaj\Tools\Db\DatabaseException;
use Kicaj\Tools\Db\DbConnector;

/**
 * Class MySQL.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class MySQL implements DbItf
{
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
     * SQL for setting timezone.
     *
     * @var string
     */
    private $sqlSetTimezone = 'SET time_zone = "%s"';

    public function dbSetup(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function dbConnect()
    {
        if ($this->isConnected) {
            return $this;
        }

        mysqli_report(MYSQLI_REPORT_STRICT);

        $this->mysql = new \mysqli(
            $this->config[DbConnector::DB_CFG_HOST],
            $this->config[DbConnector::DB_CFG_USERNAME],
            $this->config[DbConnector::DB_CFG_PASSWORD],
            $this->config[DbConnector::DB_CFG_DATABASE],
            $this->config[DbConnector::DB_CFG_PORT]);
        $this->mysql->set_charset('utf8');

        $timezone = $this->config[DbConnector::DB_CFG_TIMEZONE];
        if ($timezone) {
            $sql     = sprintf($this->sqlSetTimezone, $timezone);
            if (!$this->mysql->query($sql)) {
                $msg = sprintf('Setting timezone (%s) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.',
                    $timezone);
                throw new DatabaseException($msg);
            }
        }
        $this->isConnected = true;

        return $this;
    }

    public function useDatabase($dbName)
    {
        if (!$this->mysql->select_db($dbName)) {
            throw new DatabaseException($this->mysql->error);
        }

        return $this;
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

    public function dbDropTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        foreach ($tableNames as $tableName) {
            $sql    = sprintf('DROP TABLE `%s`', $tableName);
            $result = $this->mysql->query($sql);
            if (!$result) {
                throw new DatabaseException($this->mysql->error);
            }
        }
    }

    public function dbTruncateTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        foreach ($tableNames as $tableName) {
            $sql    = sprintf('TRUNCATE TABLE `%s`', $tableName);
            $result = $this->mysql->query($sql);
            if (!$result) {
                throw new DatabaseException($this->mysql->error);
            }
        }
    }

    public function dbCountTableRows($tableName)
    {
        $sql  = sprintf('SELECT COUNT(1) AS c FROM `%s`', $tableName);
        $resp = $this->mysql->query($sql);
        if ($resp === false) {
            throw new DatabaseException($this->mysql->error);
        }

        return (int) $resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    public function dbGetTableNames()
    {
        // TODO: check for case sensitive table names.
        $sql  = sprintf('SHOW TABLES FROM `%s`', $this->config[DbItf::DB_CFG_DATABASE]);
        $resp = $this->mysql->query($sql);
        if ($resp === false) {
            throw new DatabaseException($this->mysql->error);
        }

        $tableNames = [];
        while ($row = $resp->fetch_assoc()) {
            foreach ($row as $tableName) {
                $tableNames[] = $tableName;
            }
        }

        return $tableNames;
    }

    public function dbGetTableData($tableName)
    {
        $resp = $this->mysql->query('SELECT * FROM '.$tableName);
        if (!$resp) {
            throw new DatabaseException($this->mysql->error);
        }

        $data = [];
        while ($row = $resp->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    public function dbRunQuery($query)
    {
        $queries = is_array($query) ? $query : [$query];

        $resp = false;
        foreach ($queries as $sql) {
            $resp = $this->mysql->query($sql);
            if (!$resp) {
                throw new DatabaseException($this->mysql->error);
            }
        }

        return $resp;
    }

    public function dbLoadFixture($fixtureFormat, $fixtureData)
    {
        if ($fixtureFormat != DbItf::FIXTURE_FORMAT_SQL) {
            throw new DatabaseException('MySQL driver currently supports only SQL fixture format.');
        }

        $this->dbRunQuery($fixtureData);
    }

    public function dbClose()
    {
        $this->isConnected = false;

        if ($this->mysql) {
            $this->mysql->close();
            $this->mysql = null;
        }
    }
}
