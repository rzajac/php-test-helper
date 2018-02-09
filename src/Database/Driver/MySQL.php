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

namespace Kicaj\Test\Helper\Database\Driver;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbItf;

/**
 * MySQL database driver.
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

    /** @inheritdoc */
    public function dbSetup(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /** @inheritdoc */
    public function dbConnect()
    {
        if ($this->isConnected) {
            return $this;
        }

        mysqli_report(MYSQLI_REPORT_STRICT);

        try {
            $this->mysql = new \mysqli(
                $this->config[DbItf::DB_CFG_HOST],
                $this->config[DbItf::DB_CFG_USERNAME],
                $this->config[DbItf::DB_CFG_PASSWORD],
                $this->config[DbItf::DB_CFG_DATABASE],
                $this->config[DbItf::DB_CFG_PORT]);
            $this->mysql->set_charset('utf8');
        } catch (\Exception $e) {
            throw DatabaseEx::makeFromException($e);
        }

        $timezone = $this->config[DbItf::DB_CFG_TIMEZONE];
        if ($timezone) {
            $sql = sprintf($this->sqlSetTimezone, $timezone);
            if (!$this->mysql->query($sql)) {
                $msg = sprintf('Setting timezone (%s) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.',
                    $timezone);
                throw new DatabaseEx($msg);
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

    /** @inheritdoc */
    public function dbDropTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        foreach ($tableNames as $tableName) {
            $this->dbRunQuery(sprintf('DROP TABLE IF EXISTS `%s`', $tableName));
        }
    }

    /** @inheritdoc */
    public function dbDropViews($viewNames)
    {
        if (is_string($viewNames)) {
            $viewNames = [$viewNames];
        }

        foreach ($viewNames as $viewName) {
            $this->dbRunQuery(sprintf('DROP VIEW IF EXISTS `%s`', $viewName));
        }
    }

    /** @inheritdoc */
    public function dbTruncateTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        $existingTables = $this->dbGetTableNames();
        foreach ($tableNames as $tableName) {
            if (in_array($tableName, $existingTables)) {
                $this->dbRunQuery(sprintf('TRUNCATE TABLE `%s`', $tableName));
            }
        }
    }

    /** @inheritdoc */
    public function dbCountTableRows(string $tableName): int
    {
        $resp = $this->dbRunQuery(sprintf('SELECT COUNT(1) AS c FROM `%s`', $tableName));

        return (int)$resp->fetch_array(MYSQLI_ASSOC)['c'];
    }

    /**
     * Return table and view names form the database.
     *
     * @throws DatabaseEx
     *
     * @return array
     */
    protected function getTableNames(): array
    {
        $dbName = $this->config[DbItf::DB_CFG_DATABASE];
        $resp = $this->dbRunQuery(sprintf('SHOW FULL TABLES FROM `%s`', $dbName));

        $tableAndViewNames = [];
        while ($row = $resp->fetch_assoc()) {
            $tableAndViewNames[] = array_change_key_case($row);
        }

        return $tableAndViewNames;
    }

    /** @inheritdoc */
    public function dbGetTableNames(): array
    {
        $tableAndViewNames = $this->getTableNames();

        $dbName = mb_strtolower($this->config[DbItf::DB_CFG_DATABASE]);
        $tableNames = [];
        foreach ($tableAndViewNames as $table) {
            if ($table['table_type'] == 'BASE TABLE') {
                $tableNames[] = $table['tables_in_' . $dbName];
            }
        }

        return $tableNames;
    }

    /** @inheritdoc */
    public function dbGetViewNames(): array
    {
        $tableAndViewNames = $this->getTableNames();

        $dbName = mb_strtolower($this->config[DbItf::DB_CFG_DATABASE]);
        $viewNames = [];
        foreach ($tableAndViewNames as $table) {
            if ($table['table_type'] == 'VIEW') {
                $viewNames[] = $table['tables_in_' . $dbName];
            }
        }

        return $viewNames;
    }

    /** @inheritdoc */
    public function dbGetTableData(string $tableName): array
    {
        $resp = $this->dbRunQuery('SELECT * FROM ' . $tableName);

        $data = [];
        while ($row = $resp->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /** @inheritdoc */
    public function dbRunQuery($query)
    {
        $queries = is_array($query) ? $query : [$query];

        $resp = false;
        foreach ($queries as $sql) {
            $resp = $this->mysql->query($sql);
            if (!$resp) {
                throw new DatabaseEx($this->mysql->error);
            }
        }

        return $resp;
    }

    /** @inheritdoc */
    public function dbLoadFixture(string $fixtureFormat, $fixtureData)
    {
        if ($fixtureFormat != DbItf::FIXTURE_FORMAT_SQL) {
            throw new DatabaseEx('MySQL driver currently supports only SQL fixture format.');
        }

        $this->dbRunQuery($fixtureData);
    }

    /** @inheritdoc */
    public function dbClose()
    {
        $this->isConnected = false;

        if ($this->mysql) {
            $this->mysql->close();
            $this->mysql = null;
        }
    }
}
