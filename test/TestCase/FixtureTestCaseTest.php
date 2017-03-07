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


use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\Helper\TestCase\FixtureTestCase;

/**
 * FixtureTestCaseTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\FixtureTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class FixtureTestCaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers ::getFixturesRootPath
     */
    public function fixtureRootDirPath()
    {
        $got = FixtureTestCase::getFixturesRootPath();

        $this->assertSame('test/fixtures', $got);
    }

    /**
     * @test
     *
     * @covers ::getFixtureLoader
     */
    public function getFixtureLoader()
    {
        // When
        $fLoader = FixtureTestCase::getFixtureLoader();

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertFalse($fLoader->isDbSet());
    }

    /**
     * @test
     *
     * @covers ::getFixtureLoader
     */
    public function getFixtureLoaderDbSet()
    {
        // Given
        $db = new MySQL();
        $db->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();

        // When
        $fLoader = FixtureTestCase::getFixtureLoader($db);

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertTrue($fLoader->isDbSet());
    }

    /**
     * @test
     *
     * @covers ::getFixtureData
     */
    public function getFixtureData()
    {
        // When
        $gotData = FixtureTestCase::getFixtureData('text.txt');

        // Then
        $this->assertSame("Some text file.\nWith many lines.\n", $gotData);
    }

    /**
     * @test
     *
     * @covers ::getFixtureRawData
     */
    public function getFixtureRawData()
    {
        // When
        $gotData = FixtureTestCase::getFixtureRawData('test5.sql');

        // Then
        $this->assertSame("-- This is a comment\nINSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '500');\n", $gotData);
    }
}
