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
namespace Kicaj\Test\TestHelperTest\Loader;

use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\TestHelperTest\Helper;
use Kicaj\Tools\Exception;
use Kicaj\Tools\Helper\Str;

/**
 * Tests for FixtureLoader class.
 *
 * @coversDefaultClass Kicaj\Test\Helper\Loader\FixtureLoader
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureLoader_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture loader.
     *
     * @var FixtureLoader
     */
    protected $fixtureLoader;

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

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $db = DbGet::factory(Helper::dbGetConfig());
        $this->fixtureLoader = new FixtureLoader($db, FIXTURE_PATH);

        $this->helper = Helper::make();
        $this->helper->dbResetTestDbatabase();
    }

    /**
     * @covers ::__construct
     * @covers ::setDb
     */
    public function test___construct()
    {
        $fixtureLoader = new FixtureLoader(null, FIXTURE_PATH);
        $this->assertNotNull($fixtureLoader);

        $db = DbGet::factory(Helper::dbGetConfig());
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
        $db = DbGet::factory(Helper::dbGetConfig());
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
            ['test1.txt', FixtureLoader::FORMAT_TXT],
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
     * @covers ::loadFileFixture
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param mixed  $expected
     */
    public function test_loadFixtureFile($fixtureName, $expected)
    {
        $loaded = $this->fixtureLoader->loadFileFixture($fixtureName);
        $this->assertSame($expected, $loaded);
    }

    public function loadFixtureFileProvider()
    {
        return [
            ['test1.json', ['key1' => 'val1']],
            ['test1.sql', ['SELECT * FROM test1;', 'SELECT * FROM test2;']],
            ['multi_line.sql', ["INSERT INTO `test2`\n  (`id`, `col2`) VALUES (NULL, '200');", "INSERT INTO `test2`\n  (`id`, `col2`)\n  VALUES\n  (NULL, '202');"]],
            ['text.txt', "Some text file.\nWith many lines.\n"],
        ];
    }

    /**
     * @dataProvider loadFixtureFileErrProvider
     *
     * @covers ::loadFileFixture
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureFileErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->loadFileFixture($fixtureName);
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
     * @covers ::dbLoadFixture
     */
    public function test_loadFixture()
    {
        $this->fixtureLoader->dbLoadFixture('test2.sql');

        $this->assertSame(2, $this->helper->dbGetTableRowCount('test2'));

        $gotData = $this->helper->dbGetTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '200'],
            ['id' => '2', 'col2' => '202'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::dbLoadFixtures
     */
    public function test_loadFixtures()
    {
        $this->fixtureLoader->dbLoadFixtures(['test2.sql', 'test5.sql']);

        $this->assertSame(3, $this->helper->dbGetTableRowCount('test2'));

        $gotData = $this->helper->dbGetTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '200'],
            ['id' => '2', 'col2' => '202'],
            ['id' => '3', 'col2' => '500'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::dbLoadFixture
     *
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /.*Access denied for user.+/
     */
    public function test_loadFixtureDbConnectionError()
    {
        $dbConfig = Helper::dbGetConfig();
        $dbConfig['password'] = 'wrongOne';

        $db = DbGet::factory($dbConfig);
        $fixtureLoader = new FixtureLoader($db, FIXTURE_PATH);

        $fixtureLoader->dbLoadFixture('test2.sql');
    }

    /**
     * @dataProvider loadFixtureErrProvider
     *
     * @covers ::dbLoadFixture
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->dbLoadFixture($fixtureName);
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
