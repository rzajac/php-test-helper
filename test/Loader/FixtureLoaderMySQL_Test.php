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
use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use Kicaj\Tools\Helper\Str;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for FixtureLoader class.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Loader\FixtureLoader
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureLoaderMySQL_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture loader.
     *
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    /**
     * Database driver.
     *
     * @var DbItf
     */
    protected $dbDriver;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        MySQLHelper::resetMySQLDatabases();
        $this->dbDriver = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $this->fixtureLoader = new FixtureLoader(FIXTURE_PATH, $this->dbDriver);
    }

    /**
     * @covers ::__construct
     */
    public function test___construct()
    {
        $db = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $fixtureLoader = new FixtureLoader(FIXTURE_PATH, $db);

        $this->assertNotNull($fixtureLoader);
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
            ['test1.json', DbItf::FIXTURE_FORMAT_JSON],
            ['subDir/test2.json', DbItf::FIXTURE_FORMAT_JSON],
            ['test1.sql', DbItf::FIXTURE_FORMAT_SQL],
            ['test1.txt', DbItf::FIXTURE_FORMAT_TXT],
            ['test1.php', DbItf::FIXTURE_FORMAT_PHP],
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
    public function test_detectFormat_exception($fixturePath, $expMsg)
    {
        $thrown = false;
        $gotMsg = '';

        try {
            $this->fixtureLoader->detectFormat($fixturePath);
        } catch (\Exception $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

        $this->assertTrue($thrown);
        $this->assertSame($expMsg, $gotMsg);
    }

    public function detectFormatErrProvider()
    {
        return [
            ['notExisting.xxx', 'Unknown fixture format: xxx.'],
            ['unknown.bad', 'Unknown fixture format: bad.'],
        ];
    }

    /**
     * @dataProvider loadFixtureFileProvider
     *
     * @covers ::getFixtureData
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param mixed  $expected
     */
    public function test_loadFixtureFile($fixtureName, $expected)
    {
        $loaded = $this->fixtureLoader->getFixtureData($fixtureName);
        $this->assertSame($expected, $loaded);
    }

    public function loadFixtureFileProvider()
    {
        return [
            ['test1.json', ['key1' => 'val1']],
            ['test1.sql', ['SELECT * FROM test1;', 'SELECT * FROM test2;']],
            ['multi_line.sql', ["INSERT INTO `test2`\n  (`id`, `col2`) VALUES (NULL, '200');", "INSERT INTO `test2`\n  (`id`, `col2`)\n  VALUES\n  (NULL, '202');"]],
            ['text.txt', "Some text file.\nWith many lines.\n"],
            ['arr.php', ['test' => 1] ],
        ];
    }

    /**
     * @dataProvider loadFixtureFileErrProvider
     *
     * @covers ::loadFixtureData
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureFileErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->loadFixtureData($fixtureName);
            $thrown = false;
            $gotMessage = '';
        } catch (\Exception $e) {
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
            ['notExisting.sql', 'Fixture notExisting.sql does not exist.'],
        ];
    }

    /**
     * @covers ::loadDbFixture
     */
    public function test_loadFixture()
    {
        $this->fixtureLoader->loadDbFixture('test2.sql');

        $this->assertSame(4, $this->dbDriver->dbCountTableRows('test2'));

        $gotData = $this->dbDriver->dbGetTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '2'],
            ['id' => '2', 'col2' => '22'],
            ['id' => '3', 'col2' => '200'],
            ['id' => '4', 'col2' => '202'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::loadDbFixtures
     */
    public function test_loadFixtures()
    {
        $this->fixtureLoader->loadDbFixtures(['test2.sql', 'test5.sql']);

        $this->assertSame(5, $this->dbDriver->dbCountTableRows('test2'));

        $gotData = $this->dbDriver->dbGetTableData('test2');
        $expData = [
            ['id' => '1', 'col2' => '2'],
            ['id' => '2', 'col2' => '22'],
            ['id' => '3', 'col2' => '200'],
            ['id' => '4', 'col2' => '202'],
            ['id' => '5', 'col2' => '500'],
        ];

        $this->assertSame($expData, $gotData);
    }

    /**
     * @covers ::loadDbFixture
     *
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /.*Access denied for user.+/
     */
    public function test_loadFixtureDbConnectionError()
    {
        $dbConfig = getUnitTestDbConfig('HELPER1');
        $dbConfig['password'] = 'wrongOne';

        $db = DbGet::factory($dbConfig);
        $fixtureLoader = new FixtureLoader(FIXTURE_PATH, $db);

        $fixtureLoader->loadDbFixture('test2.sql');
    }

    /**
     * @dataProvider loadFixtureErrProvider
     *
     * @covers ::loadDbFixture
     * @covers ::loadSql
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function test_loadFixtureErr($fixtureName, $expMsg)
    {
        try {
            $this->fixtureLoader->loadDbFixture($fixtureName);
            $thrown = false;
            $gotMessage = '';
        } catch (\Exception $e) {
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

    /**
     * @covers ::loadSql
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Error opening fixture vfs://root/fixture.sql
     */
    public function test_loadSal_file_permissions_error()
    {
        $vFsRoot = vfsStream::setup();
        vfsStream::newFile('fixture.sql', 0000)->at($vFsRoot);

        $db = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $fixtureLoader = new FixtureLoader($vFsRoot->url(), $db);
        $fixtureLoader->loadDbFixture('fixture.sql');
    }
}
