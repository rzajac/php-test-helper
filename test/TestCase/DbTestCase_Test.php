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
 * Tests for DbTextCase class.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\DbTestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbTestCase_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::dbGetConfig
     */
    public function test_dbGetConfig()
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

        $this->assertSame('127.0.0.1', $dbConfig[DbConnector::DB_CFG_HOST]);
        $this->assertSame('testUser', $dbConfig[DbConnector::DB_CFG_USERNAME]);
        $this->assertSame('testUserPass', $dbConfig[DbConnector::DB_CFG_PASSWORD]);
        $this->assertSame('testHelper1', $dbConfig[DbConnector::DB_CFG_DATABASE]);
        $this->assertSame('3306', $dbConfig[DbConnector::DB_CFG_PORT]);
        $this->assertSame('UTC', $dbConfig[DbConnector::DB_CFG_TIMEZONE]);
        $this->assertSame('mysql', $dbConfig[DbConnector::DB_CFG_DRIVER]);
    }

    /**
     * @covers ::dbGetHelper
     */
    public function test_dbGetHelper()
    {
        // When
        $db = DbTestCase::dbGetHelper('HELPER1');

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Database\DbItf', $db);
    }

    /**
     * @covers ::dbGetFixtureLoader
     */
    public function test_dbGetFixtureLoader_noDb()
    {
        // When
        $fLoader = DbTestCase::dbGetFixtureLoader();

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertFalse($fLoader->isDbSet());
    }

    /**
     * @covers ::dbGetFixtureLoader
     */
    public function test_dbGetFixtureLoader_db()
    {
        // When
        $fLoader = DbTestCase::dbGetFixtureLoader('HELPER2');

        // Then
        $this->assertInstanceOf('\Kicaj\Test\Helper\Loader\FixtureLoader', $fLoader);
        $this->assertTrue($fLoader->isDbSet());
    }
}
