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
namespace Kicaj\Test\Helper\Loader;

use Kicaj\Tools\Helper\Fn;

/**
 * Schema loader for test database.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class SchemaLoader
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // TODO: implement
    }

    /**
     * Create database table.
     *
     * @param array $tableName The table name
     *
     * @return bool
     */
    public function createTable($tableName)
    {
        // TODO: implement
    }

    /**
     * Create database tables.
     *
     * @param string[] $tableNames The array of table names
     *
     * @return bool Returns true on success, false if one or more operations failed
     */
    public function createTables(array $tableNames)
    {
        $ret = true;

        foreach ($tableNames as $tableName) {
            $result = $this->createTable($tableName);
            $ret = Fn::returnIfNot($ret, false, $result);
        }

        return $ret;
    }
}
