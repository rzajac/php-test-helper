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
namespace Kicaj\Test\TestHelperTest\Database\Driver;

use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\TestHelperTest\BaseTest;

/**
 * DbGet tests.
 *
 * @coversDefaultClass Kicaj\Test\Helper\Database\Driver\MySQL
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class MySQL_Test extends BaseTest
{
    /**
     * Database driver we are testing.
     *
     * @var MySQL
     */
    protected $myMySQL;

    protected function setUp()
    {
        parent::setUp();

        $this->myMySQL = new MySQL();
        $this->myMySQL->dbSetup(static::$defDbConfig)->dbConnect();

        static::connectToDb();
        static::resetTestDb();
        static::loadTestData();
    }

    /**
     * @dataProvider connectionProvider
     *
     * @covers ::dbSetup
     * @covers ::dbConnect
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $port
     * @param bool   $connect
     * @param string $errorMsg
     */
    public function test_connection($host, $username, $password, $database, $port, $connect, $errorMsg)
    {
        // Database config
        $dbConfig = [
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $database,
            'port' => $port,
        ];

        $myMySQL = new MySQL();
        $connected = $myMySQL->dbSetup($dbConfig)->dbConnect();

        $this->assertSame($connect, $connected);

        if (!$connect) {
            $gotMsg = $myMySQL->getError()->getMessage();
        } else {
            $gotMsg = '';
        }

        $this->assertSame($errorMsg, $gotMsg);
    }

    public function connectionProvider()
    {
        return [
            ['127.0.0.1', 'root', '', 'test', 3306, true, ''],
            ['127.0.0.1', 'root', '', 'test2', 3306, false, "mysqli::mysqli(): (HY000/1049): Unknown database 'test2'"],
        ];
    }

    /**
     * @covers ::getDbTableNames
     */
    public function test_getDbTableNames()
    {
        $tableNames = $this->myMySQL->getDbTableNames();
        $this->assertSame(['test1', 'test2'], $tableNames);
    }

    /**
     * @covers ::countDbTableRows
     */
    public function test_countTableRows()
    {
        $t1Rows = $this->myMySQL->countDbTableRows('test1');
        $this->assertSame(1, $t1Rows);

        $t2Rows = $this->myMySQL->countDbTableRows('test2');
        $this->assertSame(2, $t2Rows);

        $notExisting = $this->myMySQL->countDbTableRows('notExisting');
        $this->assertSame(-1, $notExisting);
    }

    /**
     * @covers ::truncateDbTable
     *
     * @depends test_countTableRows
     */
    public function test_truncateTable()
    {
        $ret = $this->myMySQL->truncateDbTable('test2');
        $this->assertTrue($ret);

        $t2Rows = $this->myMySQL->countDbTableRows('test2');
        $this->assertSame(0, $t2Rows);

        $t1Rows = $this->myMySQL->countDbTableRows('test1');
        $this->assertSame(1, $t1Rows);
    }

    /**
     * @covers ::truncateDbTables
     *
     * @depends test_countTableRows
     */
    public function test_truncateTables()
    {
        $ret = $this->myMySQL->truncateDbTables([]);
        $this->assertTrue($ret);

        $t1Rows = $this->myMySQL->countDbTableRows('test1');
        $this->assertSame(1, $t1Rows);

        $t2Rows = $this->myMySQL->countDbTableRows('test2');
        $this->assertSame(2, $t2Rows);

        $ret = $this->myMySQL->truncateDbTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $t1Rows = $this->myMySQL->countDbTableRows('test1');
        $this->assertSame(0, $t1Rows);

        $t2Rows = $this->myMySQL->countDbTableRows('test2');
        $this->assertSame(0, $t2Rows);
    }

    /**
     * @covers ::dropDbTable
     */
    public function test_dropDbTable()
    {
        $this->assertSame(2, count(static::getTableNames()));

        $ret = $this->myMySQL->dropDbTable('test1');
        $this->assertTrue($ret);
        $this->assertSame(1, count(static::getTableNames()));

        $ret = $this->myMySQL->dropDbTable('test2');
        $this->assertTrue($ret);
        $this->assertSame(0, count(static::getTableNames()));

        $ret = $this->myMySQL->dropDbTable('notExisting');
        $this->assertTrue($ret);
        $this->assertSame(0, count(static::getTableNames()));
    }

    /**
     * @covers ::dropDbTables
     */
    public function test_dropTables()
    {
        $this->assertSame(2, count(static::getTableNames()));

        $this->myMySQL->dropDbTables(['test1', 'test2']);

        $this->assertSame(0, count(static::getTableNames()));
    }
}
