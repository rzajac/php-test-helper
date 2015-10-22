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
 * Class DbTestCase_Test.
 *
 * @coversDefaultClass Kicaj\Test\Helper\TestCase\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class ResidentFixtures_Test extends DbTestCase
{
    protected static $residentFixtures = ['test4.sql'];

    /**
     * Database helper.
     *
     * @var Helper
     */
    protected $helper;

    public static function setUpBeforeClass()
    {
        Helper::make()->dbDropAllTables();
        Helper::make()->dbResetTestDbatabase();
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        $this->helper = Helper::make();
    }

    /**
     * @covers ::setUpBeforeClass
     * @covers ::dbLoadFixtures
     */
    public function test_residentFixtures()
    {
        $this->dbLoadFixtures(['test5.sql']);

        $this->assertSame(0, $this->helper->dbGetTableRowCount('test1'));
        $this->assertSame(3, $this->helper->dbGetTableRowCount('test2'));

        $gotData = $this->helper->dbGetTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '400'],
            ['id' => '2', 'col2' => '404'],
            ['id' => '3', 'col2' => '500'],
        ];

        $this->assertSame($expData, $gotData);
    }
}
