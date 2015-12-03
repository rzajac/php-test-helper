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

use Kicaj\Tools\Db\DbConnector;
use Kicaj\Tools\Exception;

/**
 * Database interface.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
interface DbItf extends DbConnector
{
    /**
     * Drop table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function dbDropTable($tableName);

    /**
     * Drop list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * Returns true on success, false if one or more operations failed
     */
    public function dbDropTables(array $tableNames);

    /**
     * Truncate table.
     *
     * @param string $tableName The database table name
     *
     * @return bool Returns true on success
     */
    public function dbTruncateTable($tableName);

    /**
     * Truncate list of tables.
     *
     * @param array $tableNames The array of database table names
     *
     * Returns true on success, false if one or more operations failed
     */
    public function dbTruncateTables(array $tableNames);

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name
     *
     * @return int Returns -1 on error
     */
    public function dbCountTableRows($tableName);

    /**
     * Returns list of database tables.
     *
     * @return string[]
     */
    public function dbGetTableNames();

    /**
     * Run database query.
     *
     * It's used when loading fixtures. The queries must be in format understandable by the database.
     *
     * @param mixed $query
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dbRunQuery($query);
}
