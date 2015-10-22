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

use Kicaj\Test\Helper\TestCase\FixtureTestCase;
use Kicaj\Test\TestHelperTest\Helper;

/**
 * Tests for FixtureTestCase class.
 *
 * @coversDefaultClass Kicaj\Test\Helper\TestCase\FixtureTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureTestCase_Test extends FixtureTestCase
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

    protected function setUp()
    {
        parent::setUp();

        $this->helper = Helper::make()->dbResetTestDbatabase();
    }

    /**
     * @covers ::dbLoadFixtures
     */
    public function test_loadDbFixtures()
    {
        $this->assertSame(0, $this->helper->dbGetTableRowCount('test2'));

        $this->dbLoadFixtures(['test2.sql']);

        $this->assertSame(2, $this->helper->dbGetTableRowCount('test2'));
    }

    /**
     * @covers ::loadFileFixture
     */
    public function test_loadFileFixture()
    {
        $gotContents = $this->loadFileFixture('test1.sql');
        $expContents = ['SELECT * FROM test1;', 'SELECT * FROM test2;'];

        $this->assertSame($expContents, $gotContents);
    }
}
