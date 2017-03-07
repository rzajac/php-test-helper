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

use Kicaj\DbKit\DbConnector;
use Kicaj\Test\Helper\TestCase\DbTestCase;

/**
 * DbTestCaseTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbTestCaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers ::dbGetConfig
     */
    public function dbGetConfig()
    {
        // When
        $dbConfig = DbTestCase::dbGetConfig('HELPER1');

        // Then
        $this->assertSame(9, count($dbConfig));
        $this->assertArrayHasKey(DbConnector::DB_CFG_DRIVER, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_HOST, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_USERNAME, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_PASSWORD, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_DATABASE, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_PORT, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_CONNECT, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_TIMEZONE, $dbConfig);
        $this->assertArrayHasKey(DbConnector::DB_CFG_DEBUG, $dbConfig);

        $this->assertSame('192.168.42.42', $dbConfig[DbConnector::DB_CFG_HOST]);
        $this->assertSame('testUser', $dbConfig[DbConnector::DB_CFG_USERNAME]);
        $this->assertSame('testUserPass', $dbConfig[DbConnector::DB_CFG_PASSWORD]);
        $this->assertSame('testHelper1', $dbConfig[DbConnector::DB_CFG_DATABASE]);
        $this->assertSame('3306', $dbConfig[DbConnector::DB_CFG_PORT]);
        $this->assertSame('UTC', $dbConfig[DbConnector::DB_CFG_TIMEZONE]);
        $this->assertSame('mysql', $dbConfig[DbConnector::DB_CFG_DRIVER]);
    }

    /**
     * @test
     *
     * @covers ::dbGetHelper
     */
    public function dbGetHelper()
    {
        // When
        $db = DbTestCase::dbGetHelper('HELPER1');

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Database\DbItf', $db);
    }

    /**
     * @test
     *
     * @covers ::dbGetFixtureLoader
     */
    public function dbGetFixtureLoaderNoDb()
    {
        // When
        $fLoader = DbTestCase::dbGetFixtureLoader();

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertFalse($fLoader->isDbSet());
    }

    /**
     * @test
     *
     * @covers ::dbGetFixtureLoader
     */
    public function dbGetFixtureLoaderDb()
    {
        // When
        $fLoader = DbTestCase::dbGetFixtureLoader('HELPER2');

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertTrue($fLoader->isDbSet());
    }
}
