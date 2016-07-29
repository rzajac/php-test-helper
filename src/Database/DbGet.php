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

use Kicaj\DbKit\DatabaseException;
use Kicaj\DbKit\DbConnect;
use Kicaj\DbKit\DbConnector;
use Kicaj\Test\Helper\Database\Driver\MySQL;

/**
 * MySQLHelper class for getting database driver.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
final class DbGet
{
    /**
     * Database factory.
     *
     * It returns the same instance for the same config.
     *
     * @param array $dbConfig The database configuration
     *
     * @throws DatabaseException
     *
     * @return DbItf
     */
    public static function factory(array $dbConfig)
    {
        /** @var DbConnector[] $instances */
        static $instances = [];

        $key = md5(json_encode($dbConfig));

        if (isset($instances[$key])) {
            return $instances[$key];
        }

        switch (DbConnect::getDriver($dbConfig)) {
            case DbConnector::DB_DRIVER_MYSQL:
                $instances[$key] = new MySQL();
                break;

            default:
                throw new DatabaseException('Unknown database driver name: ' . DbConnect::getDriver($dbConfig));
        }

        $instances[$key]->dbSetup($dbConfig);

        if ($dbConfig[DbConnector::DB_CFG_CONNECT]) {
            $instances[$key]->dbConnect();
        }

        return $instances[$key];
    }
}
