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
use Kicaj\Test\TestHelperTest\Helper;
use Kicaj\Tools\Exception;

/**
 * DbGet tests.
 *
 * @coversDefaultClass Kicaj\Test\Helper\Database\Driver\MySQL
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class MySQL_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Database driver we are testing.
     *
     * @var MySQL
     */
    protected $testedDrv;

    /**
     * Independent database helper.
     *
     * @var Helper
     */
    protected $helper;

    public function setUp()
    {
        parent::setUp();

        $this->helper = Helper::make()->resetTestDb();

        $this->testedDrv = new MySQL();
        $this->testedDrv->dbSetup(Helper::dbGetConfig())->dbConnect();
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
     * @param string $expMsg
     */
    public function test_connection($host, $username, $password, $database, $port, $connect, $expMsg)
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

        $error = $gotMsg = $myMySQL->getError();

        if ($connect) {
            $this->assertNull($error);
        } else {
            $gotMsg = $error->getMessage();
            $this->assertContains($expMsg, $gotMsg);
        }
    }

    public function connectionProvider()
    {
        return [
            ['127.0.0.1', 'root', '', 'test', 3306, true, ''],
            ['127.0.0.1', 'root', '', 'test2', 3306, false, "Unknown database 'test2'"],
        ];
    }

    /**
     * @covers ::dbGetTableNames
     */
    public function test_getDbTableNames()
    {
        $tableNames = $this->testedDrv->dbGetTableNames();
        $this->assertSame(['test1', 'test2'], $tableNames);
    }

    /**
     * @covers ::dbCountTableRows
     */
    public function test_countTableRows()
    {
        $this->helper->loadTestData();

        $t1Rows = $this->testedDrv->dbCountTableRows('test1');
        $this->assertSame(1, $t1Rows);

        $t2Rows = $this->testedDrv->dbCountTableRows('test2');
        $this->assertSame(2, $t2Rows);

        $notExisting = $this->testedDrv->dbCountTableRows('notExisting');
        $this->assertSame(-1, $notExisting);
    }

    /**
     * @covers ::dbTruncateTable
     *
     * @depends test_countTableRows
     */
    public function test_truncateTable()
    {
        $this->helper->loadTestData();

        $ret = $this->testedDrv->dbTruncateTable('test2');
        $this->assertTrue($ret);

        $t2Rows = $this->testedDrv->dbCountTableRows('test2');
        $this->assertSame(0, $t2Rows);

        $t1Rows = $this->testedDrv->dbCountTableRows('test1');
        $this->assertSame(1, $t1Rows);
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_countTableRows
     */
    public function test_truncateTables()
    {
        $this->helper->loadTestData();

        $ret = $this->testedDrv->dbTruncateTables([]);
        $this->assertTrue($ret);

        $t1Rows = $this->testedDrv->dbCountTableRows('test1');
        $this->assertSame(1, $t1Rows);

        $t2Rows = $this->testedDrv->dbCountTableRows('test2');
        $this->assertSame(2, $t2Rows);

        $ret = $this->testedDrv->dbTruncateTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $t1Rows = $this->testedDrv->dbCountTableRows('test1');
        $this->assertSame(0, $t1Rows);

        $t2Rows = $this->testedDrv->dbCountTableRows('test2');
        $this->assertSame(0, $t2Rows);
    }

    /**
     * @covers ::dbDropTable
     */
    public function test_dropDbTable()
    {
        $this->assertSame(2, $this->helper->getTableCount());

        $ret = $this->testedDrv->dbDropTable('test1');
        $this->assertTrue($ret);
        $this->assertSame(1, $this->helper->getTableCount());

        $ret = $this->testedDrv->dbDropTable('test2');
        $this->assertTrue($ret);
        $this->assertSame(0, $this->helper->getTableCount());

        $ret = $this->testedDrv->dbDropTable('notExisting');
        $this->assertFalse($ret);
        $this->assertSame(0, $this->helper->getTableCount());
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dropTables()
    {
        $this->assertSame(2, $this->helper->getTableCount());

        $this->testedDrv->dbDropTables(['test1', 'test2']);

        $this->assertSame(0, $this->helper->getTableCount());
    }

    /**
     * @dataProvider runQueryProvider
     *
     * @covers ::dbRunQuery
     *
     * @param string $sql
     * @param string $expMsg
     */
    public function test_runQuery($sql, $expMsg)
    {
        $resp = null;

        try {
            $resp = $this->testedDrv->dbRunQuery($sql);
            $thrown = false;
            $gotMsg = '';
        } catch (Exception $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

        if ($expMsg) {
            $this->assertTrue($thrown);
            $this->assertContains($expMsg, $gotMsg);
        } else {
            $this->assertFalse($thrown);
            $this->assertNotFalse($resp);
            $this->assertSame('', $gotMsg);
        }
    }

    public function runQueryProvider()
    {
        return [
            ['SELECT * FROM test2', ''],
            [['SELECT * FROM test2', 'SELECT * FROM test2'], ''],
            ['SELECT BAD * FROM test2', 'You have an error in your SQL syntax'],
            [['SELECT * FROM test2', 'SELECT BAD * FROM test2'], 'You have an error in your SQL syntax'],
        ];
    }
}
