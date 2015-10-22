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
class Fixtures_Test extends DbTestCase
{
    protected $fixtures = ['test4.sql'];

    /**
     * Database helper.
     *
     * @var Helper
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = Helper::make()->resetTestDb();

        parent::setUp();
    }

    /**
     * @covers ::setUp
     */
    public function test_setUp()
    {
        $this->assertSame(0, $this->helper->getTableRowCount('test1'));
        $this->assertSame(2, $this->helper->getTableRowCount('test2'));
    }
}
