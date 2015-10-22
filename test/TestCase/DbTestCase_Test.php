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

    public function setUp()
    {
        parent::setUp();

        $this->helper = Helper::make()->resetTestDb();
    }

    /**
     * @covers ::dbDropTable
     * @covers ::dbDropTables
     */
    public function test_dropTables()
    {
        $this->assertSame(2, $this->helper->getTableCount());

        $ret = $this->dbDropTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $this->assertSame(0, $this->helper->getTableCount());
    }

    /**
     * @covers ::dbDropTable
     * @covers ::dbDropTables
     */
    public function test_dropTablesErr()
    {
        $ret = $this->dbDropTables(['test2']);
        $this->assertTrue($ret);

        $ret = $this->dbDropTables(['test1', 'test2']);
        $this->assertFalse($ret);
    }

    /**
     * @covers ::dbTruncateTable
     * @covers ::dbTruncateTables
     */
    public function test_dbTruncateTable()
    {
        $this->helper->loadTestData();

        $this->assertSame(1, $this->helper->getTableRowCount('test1'));
        $this->assertSame(2, $this->helper->getTableRowCount('test2'));

        $ret = $this->dbTruncateTables(['test1', 'test2']);
        $this->assertTrue($ret);

        $this->assertSame(0, $this->helper->getTableRowCount('test1'));
        $this->assertSame(0, $this->helper->getTableRowCount('test2'));
    }

    /**
     * @covers ::dbTruncateTable
     * @covers ::dbTruncateTables
     */
    public function test_dbTruncateTableErr()
    {
        $ret = $this->dbTruncateTables(['test1', 'notThere']);
        $this->assertFalse($ret);
    }

    /**
     * @covers ::dbGetTableNames
     */
    public function test_dbGetTableNames()
    {
        $got = $this->dbGetTableNames();
        $this->assertSame(['test1', 'test2'], $got);

        $this->helper->dropDbTable('test2');

        $got = $this->dbGetTableNames();
        $this->assertSame(['test1'], $got);
    }

    /**
     * @covers ::dbCountTableRows
     */
    public function test_dbCountTableRows()
    {
        $this->helper->loadTestData();

        $this->assertSame(2, $this->dbCountTableRows('test2'));
        $this->assertSame(1, $this->dbCountTableRows('test1'));
        $this->assertSame(-1, $this->dbCountTableRows('notExisting'));
    }
}
