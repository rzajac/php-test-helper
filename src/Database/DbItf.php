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

namespace Kicaj\Test\Helper\Database;

/**
 * Database interface.
 */
interface DbItf
{
    /** Database host address. */
    const DB_CFG_HOST = 'host';

    /** Database user name. */
    const DB_CFG_USERNAME = 'username';

    /** Database password. */
    const DB_CFG_PASSWORD = 'password';

    /** Database name. */
    const DB_CFG_DATABASE = 'database';

    /** Database port. */
    const DB_CFG_PORT = 'port';

    /** Connect true / false to database right away. */
    const DB_CFG_CONNECT = 'connect';

    /** Debugging true / false */
    const DB_CFG_DEBUG = 'debug';

    /** Database driver to use. One of the self::DB_DRIVER_* constants. */
    const DB_CFG_DRIVER = 'driver';

    /** The timezone to use for connection. Default UTC */
    const DB_CFG_TIMEZONE = 'timezone';

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

    /** MySQL driver. */
    const DB_DRIVER_MYSQL = 'mysql';

    /**
     * Configure database.
     *
     * @param array $dbConfig The database configuration. See self::DB_CFG_* constants.
     *
     * @throws DatabaseEx
     *
     * @return $this
     */
    public function dbSetup(array $dbConfig);

    /**
     * Connect to database.
     *
     * This method might be called multiple times but only the first call should connect to database.
     *
     * @throws DatabaseEx
     *
     * @return $this
     */
    public function dbConnect();

    /**
     * Close database connection.
     *
     * @throws DatabaseEx
     */
    public function dbClose();

    /**
     * Drop table or list of tables.
     *
     * @param string|string[] $tableNames The table name or array of table names to drop.
     *
     * @throws DatabaseEx
     */
    public function dbDropTables($tableNames);

    /**
     * Drop view or list of views.
     *
     * @param string|string[] $viewNames The view name or array of view names to drop.
     *
     * @throws DatabaseEx
     */
    public function dbDropViews($viewNames);

    /**
     * Truncate table or list of tables.
     *
     * @param string|string[] $tableNames The table name or array of table names to truncate.
     *
     * @throws DatabaseEx
     */
    public function dbTruncateTables($tableNames);

    /**
     * Get number of rows in the given table.
     *
     * @param string $tableName The database table name.
     *
     * @throws DatabaseEx
     *
     * @return int
     */
    public function dbCountTableRows(string $tableName): int;

    /**
     * Get database table data.
     *
     * @param string $tableName The database table name.
     *
     * @throws DatabaseEx
     *
     * @return array
     */
    public function dbGetTableData(string $tableName): array;

    /**
     * Return list of database tables.
     *
     * @throws DatabaseEx
     *
     * @return string[]
     */
    public function dbGetTableNames(): array;

    /**
     * Return list of database views.
     *
     * @throws DatabaseEx
     *
     * @return string[]
     */
    public function dbGetViewNames(): array;

    /**
     * Run database query.
     *
     * It's used when loading fixtures. The queries must be in format understandable by the database.
     *
     * @param mixed $query
     *
     * @throws DatabaseEx
     *
     * @return mixed
     */
    public function dbRunQuery($query);

    /**
     * Load fixture to database.
     *
     * @param string $fixtureFormat The one of FIXTURE_FORMAT_* constants.
     * @param mixed  $fixtureData   The fixture to load to database.
     *
     * @throws DatabaseEx
     */
    public function dbLoadFixture(string $fixtureFormat, $fixtureData);
}
