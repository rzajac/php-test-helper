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

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Loader\FixtureLoader;
use Kicaj\Test\Helper\TestCase\FixtureTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * FixtureLoaderTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Loader\FixtureLoader
 *
 * @author             Rafal Zajac <rzajac@gmail.com>
 */
class FixtureLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture loader.
     *
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    /**
     * Fixtures root directory.
     *
     * @var string
     */
    protected $fixturesRootPath;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->fixturesRootPath = getFixturesRootPath();
        $this->fixtureLoader = new FixtureLoader(FixtureTestCase::getFixturesRootPath());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::isDbSet
     */
    public function construct()
    {
        $this->assertNotNull($this->fixtureLoader);
        $this->assertFalse($this->fixtureLoader->isDbSet());
    }

    /**
     * @test
     *
     * @dataProvider detectFormatProvider
     *
     * @covers ::detectFormat
     *
     * @param string $fixturePath
     * @param string $expected
     *
     * @throws \Exception
     */
    public function detectFormat($fixturePath, $expected)
    {
        // When
        $got = $this->fixtureLoader->detectFormat($fixturePath);

        // Then
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
     * @test
     *
     * @dataProvider detectFormatErrProvider
     *
     * @covers ::detectFormat
     *
     * @param string $fixturePath
     * @param string $expMsg
     */
    public function detectFormatException($fixturePath, $expMsg)
    {
        // Given
        $thrown = false;
        $gotMsg = '';

        // When
        try {
            $this->fixtureLoader->detectFormat($fixturePath);
        } catch (\Exception $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

        // Then
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
     * @test
     *
     * @dataProvider loadFixtureFileProvider
     *
     * @covers ::getFixtureData
     * @covers ::loadSql
     * @covers ::loadJson
     * @covers ::loadTxt
     *
     * @param string $fixtureName
     * @param mixed  $expected
     *
     * @throws \Exception
     */
    public function loadFixtureFile($fixtureName, $expected)
    {
        // When
        $loaded = $this->fixtureLoader->getFixtureData($fixtureName);

        // Then
        $this->assertSame($expected, $loaded);
    }

    public function loadFixtureFileProvider()
    {
        return [
            ['test1.json', ['key1' => 'val1']],
            ['with_comment.json', ['key1' => 'val1']],
            ['with_comments.json', ['key1' => 'val1']],
            ['test1.sql', ['SELECT * FROM test1;', 'SELECT * FROM test2;']],
            [
                'multi_line.sql',
                [
                    "INSERT INTO `test2`\n  (`id`, `col2`) VALUES (NULL, '200');",
                    "INSERT INTO `test2`\n  (`id`, `col2`)\n  VALUES\n  (NULL, '202');",
                ],
            ],
            ['text.txt', "Some text file.\nWith many lines.\n"],
            ['arr.php', ['test' => 1]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider loadFixtureDataProvider
     *
     * @covers ::loadFixtureData
     *
     * @param string $fixturePath
     * @param string $expFixtureType
     * @param mixed  $expFixtureData
     *
     * @throws \Exception
     */
    public function loadFixtureData($fixturePath, $expFixtureType, $expFixtureData)
    {
        // When
        $fixtureData = $this->fixtureLoader->loadFixtureData($fixturePath);

        // Then
        $this->assertSame($expFixtureType, $fixtureData[0]);
        $this->assertSame($expFixtureData, $fixtureData[1]);
    }

    public function loadFixtureDataProvider()
    {
        return [
            ['test1.json', DbItf::FIXTURE_FORMAT_JSON, ['key1' => 'val1']],
            ['with_comment.json', DbItf::FIXTURE_FORMAT_JSON, ['key1' => 'val1']],
            ['test5.sql', DbItf::FIXTURE_FORMAT_SQL, ["INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '500');"]],
            ['text.txt', DbItf::FIXTURE_FORMAT_TXT, "Some text file.\nWith many lines.\n"],
            ['arr.php', DbItf::FIXTURE_FORMAT_PHP, ['test' => 1]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getFixtureRawDataProvider
     *
     * @covers ::getFixtureRawData
     *
     * @param string $fixturePath
     * @param mixed  $expFixtureData
     */
    public function getFixtureRawData($fixturePath, $expFixtureData)
    {
        // When
        $fixtureRawData = $this->fixtureLoader->getFixtureRawData($fixturePath);

        // Then
        $this->assertSame($expFixtureData, $fixtureRawData);
    }

    public function getFixtureRawDataProvider()
    {
        return [
            ['test1.json', '{"key1": "val1"}' . "\n"],
            ['with_comment.json', "-- Comment line 1\n{\"key1\": \"val1\"}" . "\n"],
            ['with_comments.json', "-- Comment line 1\n-- Comment line 2\n{\"key1\": \"val1\"}" . "\n"],
            ['test5.sql', "-- This is a comment\nINSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '500');\n"],
            ['text.txt', "Some text file.\nWith many lines.\n"],
            ['arr.php', "<?php\n\n\$fixture = [\n    'test' => 1,\n];\n\nreturn \$fixture;\n"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider loadFixtureFileErrProvider
     *
     * @covers ::loadFixtureData
     * @covers ::loadSql
     * @covers ::loadJson
     * @covers ::loadTxt
     *
     * @param string $fixtureName
     * @param string $expMsg
     */
    public function loadFixtureFileErr($fixtureName, $expMsg)
    {
        // When
        try {
            $this->fixtureLoader->loadFixtureData($fixtureName);
            $thrown = false;
            $gotMessage = '';
        } catch (\Exception $e) {
            $thrown = true;
            $gotMessage = $e->getMessage();
        }

        // Then
        $this->assertTrue($thrown);
        $this->assertSame($expMsg, $gotMessage);
    }

    public function loadFixtureFileErrProvider()
    {
        return [
            ['notExisting.sql', 'Fixture test/fixtures/notExisting.sql does not exist.'],
            ['notExisting.json', 'Fixture test/fixtures/notExisting.json does not exist.'],
            ['notExisting.txt', 'Fixture test/fixtures/notExisting.txt does not exist.'],
            ['test1bad.json', 'JSON decoding error'],
        ];
    }

    /**
     * @test
     *
     * @covers ::loadSql
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Error opening fixture vfs://root/fixture.sql
     */
    public function loadSqlFilePermissionsError()
    {
        // Given
        $vFsRoot = vfsStream::setup();
        vfsStream::newFile('fixture.sql', 0000)->at($vFsRoot);

        // When
        $fixtureLoader = new FixtureLoader($vFsRoot->url());

        // Then
        $fixtureLoader->getFixtureData('fixture.sql');
    }
}
