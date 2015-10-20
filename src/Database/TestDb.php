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
namespace Kicaj\Test\Helper\Database;

use Kicaj\Tools\Exception;
use Kicaj\Tools\Itf\Error;

/**
 * Database interface.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
interface TestDb extends Error
{
    /**
     * Configure database.
     *
     * @param array $config The database configuration
     *
     * @return TestDb
     */
    public function dbSetup(array $config);

    /**
     * Connect to database.
     *
     * @return bool Returns true on success.
     */
    public function dbConnect();

    /**
     * Drop table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function dropDbTable($tableName);

    /**
     * Drop list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * Returns true on success, false if one or more operations failed
     */
    public function dropDbTables(array $tableNames);

    /**
     * Truncate table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function truncateDbTable($tableName);

    /**
     * Truncate list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * Returns true on success, false if one or more operations failed
     */
    public function truncateDbTables(array $tableNames);

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name
     *
     * @return int
     */
    public function countDbTableRows($tableName);

    /**
     * Returns list of database tables.
     *
     * @return string[]
     */
    public function getDbTableNames();

    /**
     * Run database query.
     *
     * @param mixed $query
     *
     * @throws Exception
     *
     * @return bool
     */
    public function runQuery($query);
}
