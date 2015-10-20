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
namespace Kicaj\Test\TestHelperTest\DbTestCase;

use Kicaj\Test\TestHelperTest\BaseTest;

/**
 * Class DbTestCase_Test
 *
 * @coversDefaultClass Kicaj\Test\Helper\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbTestCase3_Test extends DbTestCase1_Test
{
    protected static $residentFixtures = ['test4.sql'];

    /**
     * @covers ::loadSQLFixtures
     */
    public function test_setUpBeforeClass()
    {
        static::loadSQLFixtures('test5.sql');

        $this->assertSame(0, BaseTest::getTableRowCount('test1'));
        $this->assertSame(3, BaseTest::getTableRowCount('test2'));
    }

    /**
     * @covers ::dbCountRows
     */
    public function test_dbCountRows()
    {
        $this->assertSame(3, static::dbCountRows('test2'));
    }

    /**
     * @covers ::dbTruncate
     *
     * @depends test_dbCountRows
     */
    public function test_dbTruncate()
    {
        static::dbTruncate('test2');

        $this->assertSame(0, BaseTest::getTableRowCount('test2'));
    }
}
