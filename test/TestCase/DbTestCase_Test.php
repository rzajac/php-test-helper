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
namespace Kicaj\Test\TestHelperTest\TestCase;

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\TestCase\DbTestCase;
use Kicaj\Test\TestHelperTest\Helper;

/**
 * Tests for DbTextCase class.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbTestCase_Test extends DbTestCase
{
    /**
     * Database helper.
     *
     * @var Helper
     */
    protected $helper;

    public static function setUpBeforeClass()
    {
        Helper::make()->dbDropAllTables();
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        $this->helper = Helper::make()->dbResetTestDbatabase();
    }

    /**
     * @covers ::dbGetConfig
     */
    public function test_dbGetConfig()
    {
        $dbConfig = self::dbGetConfig(DbItf::DB_NAME_DEFAULT);

        $this->assertSame(9, count($dbConfig));
        $this->assertArrayHasKey('driver', $dbConfig);
        $this->assertArrayHasKey('host', $dbConfig);
        $this->assertArrayHasKey('username', $dbConfig);
        $this->assertArrayHasKey('password', $dbConfig);
        $this->assertArrayHasKey('database', $dbConfig);
        $this->assertArrayHasKey('port', $dbConfig);
        $this->assertArrayHasKey('connect', $dbConfig);
        $this->assertArrayHasKey('timezone', $dbConfig);
        $this->assertArrayHasKey('debug', $dbConfig);
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dropTables()
    {
        $this->assertSame(3, $this->helper->dbGetTableCount());

        $ret = self::dbDropTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $this->assertSame(1, $this->helper->dbGetTableCount());
    }

    /**
     * @covers ::dbDropAllTables
     */
    public function test_dropAllTables()
    {
        $this->assertSame(3, $this->helper->dbGetTableCount());

        $ret = self::dbDropAllTables();
        $this->assertTrue($ret);

        $this->assertSame(0, $this->helper->dbGetTableCount());
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dropTablesErr()
    {
        $ret = self::dbDropTables(['test2']);
        $this->assertTrue($ret);

        $ret = self::dbDropTables(['test1', 'test2']);
        $this->assertFalse($ret);
    }

    /**
     * @covers ::dbTruncateTables
     */
    public function test_dbTruncateTable()
    {
        $this->helper->dbLoadTestData();

        $this->assertSame(1, $this->helper->dbGetTableRowCount('test1'));
        $this->assertSame(2, $this->helper->dbGetTableRowCount('test2'));

        $ret = self::dbTruncateTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $this->assertSame(0, $this->helper->dbGetTableRowCount('test1'));
        $this->assertSame(0, $this->helper->dbGetTableRowCount('test2'));
    }

    /**
     * @covers ::dbTruncateTables
     */
    public function test_dbTruncateTableErr()
    {
        $ret = self::dbTruncateTables(['test1', 'notThere']);
        $this->assertFalse($ret);
    }

    /**
     * @covers ::dbGetTableNames
     */
    public function test_dbGetTableNames()
    {
        $got = self::dbGetTableNames();
        $this->assertSame(['test1', 'test2', 'test3'], $got);

        $this->helper->dbDropTables('test2');

        $got = self::dbGetTableNames();
        $this->assertSame(['test1', 'test3'], $got);
    }

    /**
     * @covers ::dbCountTableRows
     */
    public function test_dbCountTableRows()
    {
        $this->helper->dbLoadTestData();

        $this->assertSame(2, self::dbCountTableRows('test2'));
        $this->assertSame(1, self::dbCountTableRows('test1'));
        $this->assertSame(-1, self::dbCountTableRows('notExisting'));
    }
}
