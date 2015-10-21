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
namespace Kicaj\Test\TestHelperTest;

use Kicaj\Test\Helper\Database\TestDb;
use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\FixtureLoader;
use Kicaj\Tools\Exception;
use Kicaj\Tools\Helper\Str;

/**
 * Tests for FixtureLoader class.
 *
 * @coversDefaultClass Kicaj\Test\Helper\FixtureLoader
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureLoader_Test extends BaseTest
{
    /**
     * The database interface.
     *
     * @var TestDb
     */
    protected $db;

    /**
     * Fixture loader.
     *
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    /**
     * @throws Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->db = DbGet::factory(static::$defDbConfig);
        $this->fixtureLoader = new FixtureLoader($this->db, FIXTURE_PATH);

        static::connectToDb();
        static::resetTestDb();
    }

    /**
     * @covers ::__construct
     * @covers ::setDb
     */
    public function test___construct()
    {
        $fixtureLoader = new FixtureLoader(null, FIXTURE_PATH);
        $this->assertNotNull($fixtureLoader);

        $db = DbGet::factory(static::$defDbConfig);
        $fixtureLoader->setDb($db);
    }

    /**
     * @covers ::setDb
     *
     * @expectedException Exception
     * @expectedExceptionMessage cannot set database twice
     */
    public function test_setDbErr()
    {
        $db = DbGet::factory(static::$defDbConfig);
        $fixtureLoader = new FixtureLoader($db, FIXTURE_PATH);

        $this->assertNotNull($fixtureLoader);

        // Set already set database
        $fixtureLoader->setDb($db);
    }

    /**
     * @dataProvider detectFormatProvider
     *
     * @covers ::detectFormat
     *
     * @param string $fixturePath
     * @param string $expected
     */
    public function test_detectFormat($fixturePath, $expected)
    {
        $got = $this->fixtureLoader->detectFormat($fixturePath);
        $this->assertSame($expected, $got);
    }

    public function detectFormatProvider()
    {
        return [
            ['test1.json', FixtureLoader::FORMAT_JSON],
            ['subDir/test2.json', FixtureLoader::FORMAT_JSON],
            ['test1.sql', FixtureLoader::FORMAT_SQL],
        ];
    }

    /**
     * @dataProvider detectFormatErrProvider
     *
     * @covers ::detectFormat
     *
     * @param string $fixturePath
     * @param string $expMsg
     */
    public function test_detectFormatEx($fixturePath, $expMsg)
    {
        $thrown = false;
        $gotMsg = '';

        try {
            $this->fixtureLoader->detectFormat($fixturePath);
        } catch (Exception $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

        $this->assertTrue($thrown);
        $this->assertSame($expMsg, $gotMsg);
    }

    public function detectFormatErrProvider()
    {
        return [
            ['notExisting.xxx', 'unknown format: xxx'],
            ['unknown.bad', 'unknown format: bad'],
        ];
    }

    /**
     * @dataProvider loadFixtureFileProvider
     *
     * @covers ::loadFixtureFile
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param mixed  $expected
     */
    public function test_loadFixtureFile($fixtureName, $expected)
    {
        $loaded = $this->fixtureLoader->loadFixtureFile($fixtureName);
        $this->assertSame($expected, $loaded);
    }

    public function loadFixtureFileProvider()
    {
        return [
            ['test1.json', ['key1' => 'val1']],
            ['test1.sql', ['SELECT * FROM test1;', 'SELECT * FROM test2;']],
        ];
    }

    /**
     * @dataProvider loadFixtureFileErrProvider
     *
     * @covers ::loadFixtureFile
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureFileErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->loadFixtureFile($fixtureName);
            $thrown = false;
            $gotMessage = '';
        } catch (Exception $e) {
            $thrown = true;
            $gotMessage = $e->getMessage();

            // Make path relative to FIXTURE_PATH
            $gotMessage = str_replace(FIXTURE_PATH.DIRECTORY_SEPARATOR, '', $gotMessage);
        }

        $this->assertTrue($thrown);
        $this->assertSame($expMsg, $gotMessage);
    }

    public function loadFixtureFileErrProvider()
    {
        return [
            ['notExisting.sql', 'fixture notExisting.sql does not exist'],
        ];
    }

    /**
     * @covers ::loadFixture
     */
    public function test_loadFixture()
    {
        $this->fixtureLoader->loadFixture('test2.sql');

        $this->assertSame(2, static::getTableRowCount('test2'));

        $gotData = BaseTest::getTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '200'],
            ['id' => '2', 'col2' => '202'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::loadFixtures
     */
    public function test_loadFixtures()
    {
        $this->fixtureLoader->loadFixtures(['test2.sql', 'test5.sql']);

        $this->assertSame(3, static::getTableRowCount('test2'));

        $gotData = BaseTest::getTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '200'],
            ['id' => '2', 'col2' => '202'],
            ['id' => '3', 'col2' => '500'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::loadFixture
     *
     * @expectedException Exception
     * @expectedExceptionMessage mysqli::mysqli(): (HY000/1045): Access denied for user 'unitTest'@'localhost' (using password: YES)
     */
    public function test_loadFixtureDbConnectionError()
    {
        static::$defDbConfig['password'] = 'wrongOne';
        $db = DbGet::factory(static::$defDbConfig);
        $fixtureLoader = new FixtureLoader($db, FIXTURE_PATH);

        $fixtureLoader->loadFixture('test2.sql');
    }

    /**
     * @dataProvider loadFixtureErrProvider
     *
     * @covers ::loadFixture
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->loadFixture($fixtureName);
            $thrown = false;
            $gotMessage = '';
        } catch (Exception $e) {
            $thrown = true;
            $gotMessage = $e->getMessage();

            // Make path relative to FIXTURE_PATH
            $gotMessage = str_replace(FIXTURE_PATH.DIRECTORY_SEPARATOR, '', $gotMessage);
        }

        $this->assertTrue($thrown);
        $this->assertTrue(Str::startsWith($gotMessage, $expMsg));
    }

    public function loadFixtureErrProvider()
    {
        return [
            ['bad.sql', 'You have an error in your SQL syntax'],
        ];
    }
}
