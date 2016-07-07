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
use Kicaj\Tools\Db\DbConnector;
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
     * @param array $config The database configuration.
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
     * @throws \Exception
     *
     * @return bool Returns true on success.
     */
    public function dbConnect()
    {
        if ($this->isConnected) {
            return true;
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

            $timezone = $this->config[DbConnector::DB_CFG_TIMEZONE];
            if ($timezone)
            {
                $sql = sprintf('SET time_zone = "%s"', $timezone);
                $success = $this->mysql->query($sql);
                if (!$success) {
                    $msg = sprintf('Setting timezone (%s) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.', $timezone);
                    throw new \Exception($msg);
                }
            }
            $this->isConnected = true;
        } catch (\Exception $e) {
            $this->isConnected = false;
            return $this->addError($e);
        }

        return true;
    }

    /**
     * The database to use if not specified in database config.
     *
     * @param string $dbName The database name to use.
     *
     * @return $this
     */
    public function useDatabase($dbName)
    {
        if (!$this->mysql->select_db($dbName)) {
            $this->addError('Could not change the database to: '.$dbName);
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

        $ret = true;
        foreach ($tableNames as $tableName) {
            $sql = sprintf('DROP TABLE `%s`', $tableName);
            $result = (bool) $this->mysql->query($sql);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }

    public function dbTruncateTables($tableNames)
    {
        if (is_string($tableNames)) {
            $tableNames = [$tableNames];
        }

        $ret = true;
        foreach ($tableNames as $tableName) {
            $sql = sprintf('TRUNCATE TABLE `%s`', $tableName);
            $result = $this->mysql->query($sql);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }

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

    public function dbRunQuery($query)
    {
        $queries = is_array($query) ? $query : [$query];

        $resp = false;
        foreach ($queries as $sql) {
            $resp = $this->mysql->query($sql);
            if (!$resp) {
                throw new \Exception($this->mysql->error);
            }
        }

        return $resp;
    }

    public function dbLoadFixture($fixtureFormat, $fixture)
    {
        if ($fixtureFormat != DbItf::FIXTURE_FORMAT_SQL) {
            throw new \Exception('MySQL driver currently supports only SQL fixture format.');
        }

        $this->dbRunQuery($fixture);
    }

    public function dbClose()
    {
        $this->isConnected = false;
        return $this->mysql->close();
    }
}
