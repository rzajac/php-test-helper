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
use Kicaj\Tools\Helper\Str;

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

        try {
            $this->mysql = new \mysqli(
                $this->config[DbConnector::DB_CFG_HOST],
                $this->config[DbConnector::DB_CFG_USERNAME],
                $this->config[DbConnector::DB_CFG_PASSWORD],
                $this->config[DbConnector::DB_CFG_DATABASE],
                $this->config[DbConnector::DB_CFG_PORT]);
            $this->mysql->set_charset('utf8');
        } catch (\Exception $e) {
            throw DatabaseException::makeFromException($e);
        }

        $timezone = $this->config[DbConnector::DB_CFG_TIMEZONE];
        if ($timezone) {
            $sql = sprintf($this->sqlSetTimezone, $timezone);
            if (!$this->mysql->query($sql)) {
                $msg = sprintf('Setting timezone (%s) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.',
                    $timezone);
                throw new DatabaseException($msg);
            }
        }
        $this->isConnected = true;

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
            try {
                $this->dbRunQuery(sprintf('DROP TABLE `%s`', $tableName));
            } catch (DatabaseException $dbe) {
                if (Str::contains($dbe->getMessage(), 'Unknown table')) {
                    // Try dropping view.
                    $this->dbRunQuery(sprintf('DROP VIEW `%s`', $tableName));
                } else {
                    throw $dbe;
                }
            }
        }
    }

    public function dbTruncateTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        foreach ($tableNames as $tableName) {
            $this->dbRunQuery(sprintf('TRUNCATE TABLE `%s`', $tableName));
        }
    }

    public function dbCountTableRows($tableName)
    {
        $resp = $this->dbRunQuery(sprintf('SELECT COUNT(1) AS c FROM `%s`', $tableName));

        return (int) $resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    public function dbGetTableNames()
    {
        $resp = $this->dbRunQuery(sprintf('SHOW TABLES FROM `%s`', $this->config[DbItf::DB_CFG_DATABASE]));

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
        $resp = $this->dbRunQuery('SELECT * FROM ' . $tableName);

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
