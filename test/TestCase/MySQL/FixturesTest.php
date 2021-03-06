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

use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\Helper\TestCase\FixtureTestCase;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use PHPUnit\Framework\TestCase;

/**
 * FixturesTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\FixtureTestCase
 */
class FixturesTest extends TestCase
{
    /**
     * Database helper.
     *
     * @var MySQL
     */
    protected $dbDriver;

    /**
     * The fixture loader.
     *
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    /** @inheritdoc */
    public function setUp()
    {
        MySQLHelper::resetMySQLDatabases();
        $this->dbDriver = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $this->fixtureLoader = new FixtureLoader(FixtureTestCase::getFixturesRootPath(), $this->dbDriver);

        parent::setUp();
    }

    /**
     * @test
     *
     * @covers ::setUp
     *
     * @throws \Kicaj\Test\Helper\Loader\FixtureLoaderEx
     * @throws \Kicaj\Test\Helper\Database\DatabaseEx
     */
    public function setUpTest()
    {
        // Given
        $this->assertSame(1, $this->dbDriver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->dbDriver->dbCountTableRows('test2'));

        // When
        $this->fixtureLoader->loadDbFixture('test4.sql');

        // Then
        $this->assertSame(1, $this->dbDriver->dbCountTableRows('test1'));
        $this->assertSame(4, $this->dbDriver->dbCountTableRows('test2'));
    }
}
