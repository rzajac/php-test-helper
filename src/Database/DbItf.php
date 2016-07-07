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

/**
 * Database interface.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
interface DbItf extends DbConnector
{
    /** The default database name. */
    const DB_NAME_DEFAULT = 'DEFAULT';

    /** Fixture in JSON format */
    const FIXTURE_FORMAT_JSON = 'json';

    /** Fixture in SQL format */
    const FIXTURE_FORMAT_SQL = 'sql';

    /** Fixture in TXT format */
    const FIXTURE_FORMAT_TXT = 'txt';

    /** Fixture in PHP format */
    const FIXTURE_FORMAT_PHP = 'php';

    /**
     * Drop table or list of tables.
     *
     * @param string|string[] $tableNames The table name or array of table names to drop.
     *
     * @return bool Return true on success, false if one or more operations failed.
     */
    public function dbDropTables($tableNames);

    /**
     * Truncate table or list of tables.
     *
     * @param string|string[] $tableNames The table name or array of table names to truncate.
     *
     * @return bool Return true on success, false if one or more operations failed.
     */
    public function dbTruncateTables($tableNames);

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name.
     *
     * @return int Return -1 on error.
     */
    public function dbCountTableRows($tableName);

    /**
     * Return list of database tables.
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
     * @throws \Exception
     *
     * @return mixed
     */
    public function dbRunQuery($query);

    /**
     * Load fixture to database.
     *
     * @param string $fixtureFormat The one of FIXTURE_FORMAT_* constants.
     * @param mixed  $fixture       The fixture to load to database.
     *
     * @throws \Exception
     */
    public function dbLoadFixture($fixtureFormat, $fixture);
}
