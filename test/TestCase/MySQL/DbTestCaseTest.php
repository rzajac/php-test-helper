<?php declare(strict_types=1);

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

namespace Kicaj\Test\TestHelperTest\MySQL\TestCase;

use Kicaj\Test\Helper\TestCase\DbTestCase;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use PHPUnit\Framework\TestCase;

/**
 * DbTestCaseTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\DbTestCase
 */
class DbTestCaseTest extends TestCase
{
    /** @inheritdoc */
    public static function setUpBeforeClass()
    {
        MySQLHelper::resetMySQLDatabases();
    }

    /**
     * @test
     *
     * @covers ::dbTableExists
     *
     * @throws \Kicaj\Test\Helper\Database\DatabaseEx
     */
    public function dbTableExistsDoesNotExist()
    {
        // When
        $exists = DbTestCase::dbTableExists('HELPER1', '__not_exists__');

        // Then
        $this->assertFalse($exists);
    }

    /**
     * @test
     *
     * @covers ::dbTableExists
     *
     * @throws \Kicaj\Test\Helper\Database\DatabaseEx
     */
    public function dbTableExistsExist()
    {
        // When
        $exists = DbTestCase::dbTableExists('HELPER1', 'test1');

        // Then
        $this->assertTrue($exists);
    }
}
