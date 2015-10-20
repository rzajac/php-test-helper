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

use Kicaj\Test\Helper\DbTestCase;
use Kicaj\Test\TestHelperTest\BaseTest;

/**
 * Class DbTestCase_Test
 *
 * @coversDefaultClass Kicaj\Test\Helper\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
abstract class DbTestCase1_Test extends DbTestCase
{
    public static function setUpBeforeClass()
    {
        BaseTest::connectToDb();
        BaseTest::resetTestDb();
        BaseTest::truncateTestTables();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        BaseTest::disconnectFromDb();
    }
}
