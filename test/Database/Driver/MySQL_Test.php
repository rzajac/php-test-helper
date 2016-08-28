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
namespace Kicaj\Test\TestHelperTest\Database\Driver;

use Kicaj\DbKit\DatabaseException;
use Kicaj\DbKit\DbConnector;
use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Database\Driver\_WhatMysqliReport;
use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use ReflectionClass;

/**
 * DbGet tests.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\Driver\MySQL
 *
 * @author             Rafal Zajac <rzajac@gmail.com>
 */
class MySQL_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Database driver we are testing.
     *
     * @var MySQL
     */
    protected $driver;

    /**
     * Database driver.
     *
     * @var \mysqli
     */
    protected $mysql;

    public static function setUpBeforeClass()
    {
        /** @noinspection PhpIncludeInspection */
        require_once getFixturesRootPath() . '/inject_mysqli_report.php';
    }

    public function setUp()
    {
        _WhatMysqliReport::$throw = false;

        // NOTE: THIS IS RESETTING DATABASE TO KNOWN STATE BEFORE EACH TEST!
        MySQLHelper::resetMySQLDatabases();

        // Connect to default database.
        $this->driver = new MySQL();
        $this->driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();
    }

    protected function tearDown()
    {
        _WhatMysqliReport::$throw = false;
    }

    /**
     * @dataProvider connectionProvider
     *
     * @covers ::dbSetup
     * @covers ::dbConnect
     * @covers ::isConnected
     *
     * @param string $host     The database host.
     * @param string $username The database username.
     * @param string $password The database password.
     * @param string $database The database name.
     * @param string $port     The database port.
     * @param string $timezone The timezone to set for connection.
     * @param string $errorMsg The expected error message.
     */
    public function test_connection($host, $username, $password, $database, $port, $timezone, $errorMsg)
    {
        // Given
        $this->driver = null;
        $thrown = false;

        $dbConfig = [
            DbConnector::DB_CFG_HOST     => $host,
            DbConnector::DB_CFG_USERNAME => $username,
            DbConnector::DB_CFG_PASSWORD => $password,
            DbConnector::DB_CFG_DATABASE => $database,
            DbConnector::DB_CFG_PORT     => $port,
            DbConnector::DB_CFG_TIMEZONE => $timezone,
        ];

        // When
        $driver = new MySQL();

        // Then
        try {
            $db = $driver->dbSetup($dbConfig)->dbConnect();

            $this->assertSame($driver, $db);
            // Call method that is actually doing something with database.
            $driver->dbGetTableNames();
        } catch (DatabaseException $e) {
            $thrown = true;
            $this->assertFalse('' == $errorMsg, 'Did not expect to see error: ' . $e->getMessage());
        } finally {
            $this->assertFalse('' !== $errorMsg && false === $thrown, 'Expected to see error: ' . $errorMsg);
        }
    }

    public function connectionProvider()
    {
        return [
            [
                $GLOBALS['TEST_DB_HELPER1_HOST'],
                $GLOBALS['TEST_DB_HELPER1_USERNAME'],
                $GLOBALS['TEST_DB_HELPER1_PASSWORD'],
                $GLOBALS['TEST_DB_HELPER1_DATABASE'],
                3306,
                'UTC',
                '',
            ],

            [
                $GLOBALS['TEST_DB_HELPER1_HOST'],
                $GLOBALS['TEST_DB_HELPER1_USERNAME'],
                $GLOBALS['TEST_DB_HELPER1_PASSWORD'],
                $GLOBALS['TEST_DB_HELPER1_DATABASE'],
                3306,
                'NOT_EXISTING',
                '/Setting timezone \(NOT_EXISTING\) for MySQL driver failed.*/',
            ],

            [
                $GLOBALS['TEST_DB_HELPER1_HOST'],
                $GLOBALS['TEST_DB_HELPER1_USERNAME'],
                $GLOBALS['TEST_DB_HELPER1_PASSWORD'],
                'not_existing',
                3306,
                'UTC',
                "/Access denied for user .* to database 'not_existing'/",
            ],
        ];
    }

    /**
     * @covers ::dbConnect
     *
     * @expectedException \Kicaj\DbKit\DatabaseException
     * @expectedExceptionMessage Setting timezone (UTC) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.
     */
    public function test_dbConnect_timezoneError()
    {
        // Given
        $driver = new MySQL();
        $reflection = new ReflectionClass($driver);
        $reflectionProperty = $reflection->getProperty('sqlSetTimezone');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($driver, 'BAD SQL IS ENOUGH');

        // Then
        $driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();
    }

    /**
     * @covers ::dbConnect
     */
    public function test_dbConnect_calledTwice()
    {
        // Given
        _WhatMysqliReport::$throw = true;

        // Then
        $driver = $this->driver->dbConnect();
        $this->assertSame($this->driver, $driver);
    }

    /**
     * @covers ::dbConnect
     * @covers ::isConnected
     */
    public function test_isConnected_notConnected()
    {
        // When
        $driver = new MySQL();

        // Then
        $this->assertFalse($driver->isConnected());
    }

    /**
     * @covers ::dbConnect
     * @covers ::isConnected
     */
    public function test_isConnected_connected()
    {
        // When
        $driver = new MySQL();
        $driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();

        // Then
        $this->assertTrue($driver->isConnected());
    }

    /**
     * @covers ::dbGetTableNames
     * @covers ::getTableNames
     */
    public function test_dbGetTableNames()
    {
        // When
        $tableNames = $this->driver->dbGetTableNames();

        // Then
        $this->assertSame(['test1', 'test2', 'test3'], $tableNames);
    }

    /**
     * @covers ::dbGetViewNames
     */
    public function test_dbGetViewNames()
    {
        // When
        $viewNames = $this->driver->dbGetViewNames();

        // Then
        $this->assertSame(['my_view'], $viewNames);
    }

    /**
     * @covers ::dbDropViews
     * @covers ::dbGetViewNames
     */
    public function test_dbDropViews()
    {
        // Given
        $this->driver->dbDropViews('my_view');

        // When
        $viewNames = $this->driver->dbGetViewNames();

        // Then
        $this->assertSame([], $viewNames);
    }

    /**
     * @covers ::dbGetTableNames
     *
     * @expectedException \Kicaj\DbKit\DatabaseException
     * @expectedExceptionMessageRegExp /Incorrect database name/
     */
    public function test_dbGetTableNames_error()
    {
        // Given
        $driver = new MySQL();

        // When
        $driver->dbSetup(getUnitTestDbConfig('HELPER_NOT_THERE'))->dbConnect();

        // Then
        $driver->dbGetTableNames();
    }

    /**
     * @covers ::dbCountTableRows
     *
     * @expectedException \Kicaj\DbKit\DatabaseException
     * @expectedExceptionMessageRegExp /Table .* doesn't exist/
     */
    public function test_dbCountTableRows_not_existing_table()
    {
        $this->driver->dbCountTableRows('notExisting');
    }

    /**
     * @covers ::dbCountTableRows
     */
    public function test_dbCountTableRows()
    {
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_dbCountTableRows
     */
    public function test_dbTruncateTables_emptyArray()
    {
        // When
        $this->driver->dbTruncateTables([]);

        // Then - no changes
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_dbCountTableRows
     */
    public function test_dbTruncateTables_array()
    {
        // When
        $this->driver->dbTruncateTables(['test2', 'test3']);

        // Then
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_dbCountTableRows
     */
    public function test_dbTruncateTables_string()
    {
        // When
        $this->driver->dbTruncateTables('test1');

        // Then
        $this->assertSame(0, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dbDropTables_multiple()
    {
        // When
        $this->driver->dbDropTables(['test1', 'test3']);

        // Then
        $this->assertSame(1, count($this->driver->dbGetTableNames()));
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dbDropTables_single()
    {
        // When
        $this->driver->dbDropTables('test2');

        // Then
        $this->assertSame(2, count($this->driver->dbGetTableNames()));
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dbDropTables_view()
    {
        // When
        $this->driver->dbDropTables('my_view');

        // Then
        $this->assertSame(3, count($this->driver->dbGetTableNames()));
    }

    /**
     * @covers ::dbGetTableData
     */
    public function test_dbGetTableData()
    {
        // When
        $got = $this->driver->dbGetTableData('test2');

        // Then
        $expected = [
            ['id' => '1', 'col2' => '2'],
            ['id' => '2', 'col2' => '22'],
        ];
        $this->assertSame($expected, $got);
    }

    /**
     * @covers ::dbGetTableData
     *
     * @expectedException \Kicaj\DbKit\DatabaseException
     * @expectedExceptionMessageRegExp /Table .* doesn't exist/
     */
    public function test_dbGetTableData_error()
    {
        $this->driver->dbGetTableData('not_existing');
    }

    /**
     * @covers ::dbLoadFixture
     */
    public function test_dbLoadFixture()
    {
        // Given
        $fixture = [
            "INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '600')",
            "INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '700')",
        ];

        // When
        $this->driver->dbLoadFixture(DbItf::FIXTURE_FORMAT_SQL, $fixture);
        $got = $this->driver->dbGetTableData('test2');

        // Then
        $expected = [
            ['id' => '1', 'col2' => '2'],
            ['id' => '2', 'col2' => '22'],
            ['id' => '3', 'col2' => '600'],
            ['id' => '4', 'col2' => '700'],
        ];
        $this->assertSame($expected, $got);
    }

    /**
     * @covers ::dbLoadFixture
     *
     * @expectedException \Kicaj\DbKit\DatabaseException
     * @expectedExceptionMessage MySQL driver currently supports only SQL fixture format.
     */
    public function test_dbLoadFixture_notSupportedFormat()
    {
        // When
        $fixture = '{"key1": "val1"}';

        // Then
        $this->driver->dbLoadFixture(DbItf::FIXTURE_FORMAT_JSON, $fixture);
    }

    /**
     * @dataProvider runQueryProvider
     *
     * @covers ::dbRunQuery
     *
     * @param string $sql
     * @param string $expMsg
     */
    public function test_runQuery($sql, $expMsg)
    {
        // Given
        $resp = null;

        // When
        try {
            $resp = $this->driver->dbRunQuery($sql);
            $thrown = false;
            $gotMsg = '';
        } catch (DatabaseException $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

        // Then
        if ($expMsg) {
            $this->assertTrue($thrown);
            $this->assertContains($expMsg, $gotMsg);
        } else {
            $this->assertFalse($thrown);
            $this->assertNotFalse($resp);
            $this->assertSame('', $gotMsg);
        }
    }

    public function runQueryProvider()
    {
        return [
            ['SELECT * FROM test2', ''],
            [['SELECT * FROM test2', 'SELECT * FROM test2'], ''],
            ['SELECT BAD * FROM test2', 'You have an error in your SQL syntax'],
            [['SELECT * FROM test2', 'SELECT BAD * FROM test2'], 'You have an error in your SQL syntax'],
        ];
    }

    /**
     * @covers ::dbClose
     * @covers ::dbConnect
     */
    public function test_dbClose()
    {
        // When
        $this->driver->dbClose();

        // Then
        $this->assertFalse($this->driver->isConnected());
    }
}

