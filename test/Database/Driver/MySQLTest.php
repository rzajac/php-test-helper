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

namespace Kicaj\Test\TestHelperTest\Database\Driver;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Database\Driver\_WhatMysqliReport;
use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * MySQLTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\Driver\MySQL
 */
class MySQLTest extends TestCase
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

    /** @inheritdoc */
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
     * @test
     *
     * @covers ::dbSetup
     * @covers ::dbConnect
     * @covers ::isConnected
     *
     * @dataProvider connectionProvider
     *
     * @param string $host     The database host.
     * @param string $username The database username.
     * @param string $password The database password.
     * @param string $database The database name.
     * @param string $port     The database port.
     * @param string $timezone The timezone to set for connection.
     * @param string $errorMsg The expected error message.
     */
    public function connection($host, $username, $password, $database, $port, $timezone, $errorMsg)
    {
        // Given
        $this->driver = null;
        $thrown = false;

        $dbConfig = [
            DbItf::DB_CFG_HOST => $host,
            DbItf::DB_CFG_USERNAME => $username,
            DbItf::DB_CFG_PASSWORD => $password,
            DbItf::DB_CFG_DATABASE => $database,
            DbItf::DB_CFG_PORT => $port,
            DbItf::DB_CFG_TIMEZONE => $timezone,
        ];

        // When
        $driver = new MySQL();

        // Then
        try {
            $db = $driver->dbSetup($dbConfig)->dbConnect();

            $this->assertSame($driver, $db);
            // Call method that is actually doing something with database.
            $driver->dbGetTableNames();
        } catch (DatabaseEx $e) {
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
     * @test
     *
     * @covers ::dbConnect
     *
     * @expectedException \Kicaj\Test\Helper\Database\DatabaseEx
     * @expectedExceptionMessage Setting timezone (UTC) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.
     *
     * @throws \ReflectionException
     */
    public function dbConnectTimezoneError()
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
     * @test
     *
     * @covers ::dbConnect
     *
     * @throws DatabaseEx
     */
    public function dbConnectCalledTwice()
    {
        // Given
        _WhatMysqliReport::$throw = true;

        // Then
        $driver = $this->driver->dbConnect();
        $this->assertSame($this->driver, $driver);
    }

    /**
     * @test
     *
     * @covers ::dbConnect
     * @covers ::isConnected
     */
    public function isConnectedNotConnected()
    {
        // When
        $driver = new MySQL();

        // Then
        $this->assertFalse($driver->isConnected());
    }

    /**
     * @test
     *
     * @covers ::dbConnect
     * @covers ::isConnected
     *
     * @throws DatabaseEx
     */
    public function isConnectedConnected()
    {
        // When
        $driver = new MySQL();
        $driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();

        // Then
        $this->assertTrue($driver->isConnected());
    }

    /**
     * @test
     *
     * @covers ::dbGetTableNames
     * @covers ::getTableNames
     */
    public function dbGetTableNames()
    {
        // When
        $tableNames = $this->driver->dbGetTableNames();

        // Then
        $this->assertSame(['test1', 'test2', 'test3'], $tableNames);
    }

    /**
     * @test
     *
     * @covers ::dbGetViewNames
     */
    public function dbGetViewNames()
    {
        // When
        $viewNames = $this->driver->dbGetViewNames();

        // Then
        $this->assertSame(['my_view'], $viewNames);
    }

    /**
     * @test
     *
     * @covers ::dbDropViews
     * @covers ::dbGetViewNames
     */
    public function dbDropViews()
    {
        // Given
        $this->driver->dbDropViews('my_view');

        // When
        $viewNames = $this->driver->dbGetViewNames();

        // Then
        $this->assertSame([], $viewNames);
    }

    /**
     * @test
     *
     * @covers ::dbGetTableNames
     *
     * @expectedException \Kicaj\Test\Helper\Database\DatabaseEx
     */
    public function dbGetTableNamesError()
    {
        // Given
        $driver = new MySQL();

        // When
        $driver->dbSetup(getUnitTestDbConfig('HELPER_NOT_THERE'))->dbConnect();

        // Then
        $driver->dbGetTableNames();
    }

    /**
     * @test
     *
     * @covers ::dbCountTableRows
     *
     * @expectedException \Kicaj\Test\Helper\Database\DatabaseEx
     * @expectedExceptionMessageRegExp /Table .* doesn't exist/
     */
    public function dbCountTableRowsNotExistingTable()
    {
        $this->driver->dbCountTableRows('notExisting');
    }

    /**
     * @test
     *
     * @covers ::dbCountTableRows
     */
    public function dbCountTableRows()
    {
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @test
     *
     * @covers ::dbTruncateTables
     *
     * @depends dbCountTableRows
     */
    public function dbTruncateTablesEmptyArray()
    {
        // When
        $this->driver->dbTruncateTables([]);

        // Then - no changes
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @test
     *
     * @covers ::dbTruncateTables
     *
     * @depends dbCountTableRows
     */
    public function dbTruncateTablesNotExistingTable()
    {
        // When
        $this->driver->dbTruncateTables('not_existing');

        // Then - no changes
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @test
     *
     * @covers ::dbTruncateTables
     *
     * @depends dbCountTableRows
     */
    public function dbTruncateTables_array()
    {
        // When
        $this->driver->dbTruncateTables(['test2', 'test3']);

        // Then
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @test
     *
     * @covers ::dbTruncateTables
     *
     * @depends dbCountTableRows
     */
    public function dbTruncateTablesString()
    {
        // When
        $this->driver->dbTruncateTables('test1');

        // Then
        $this->assertSame(0, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @test
     *
     * @covers ::dbDropTables
     */
    public function dbDropTablesMultiple()
    {
        // When
        $this->driver->dbDropTables(['test1', 'test3']);

        // Then
        $this->assertSame(1, count($this->driver->dbGetTableNames()));
    }

    /**
     * @test
     *
     * @covers ::dbDropTables
     */
    public function dbDropTablesSingle()
    {
        // When
        $this->driver->dbDropTables('test2');

        // Then
        $this->assertSame(2, count($this->driver->dbGetTableNames()));
    }

    /**
     * @test
     *
     * @covers ::dbDropTables
     */
    public function dbDropTablesView()
    {
        // When
        $this->driver->dbDropTables('my_view');

        // Then
        $this->assertSame(3, count($this->driver->dbGetTableNames()));
    }

    /**
     * @test
     *
     * @covers ::dbGetTableData
     */
    public function dbGetTableData()
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
     * @test
     *
     * @covers ::dbGetTableData
     *
     * @expectedException \Kicaj\Test\Helper\Database\DatabaseEx
     * @expectedExceptionMessageRegExp /Table .* doesn't exist/
     */
    public function dbGetTableDataError()
    {
        $this->driver->dbGetTableData('not_existing');
    }

    /**
     * @test
     *
     * @covers ::dbLoadFixture
     *
     * @throws DatabaseEx
     */
    public function dbLoadFixture()
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
     * @test
     *
     * @covers ::dbLoadFixture
     *
     * @expectedException \Kicaj\Test\Helper\Database\DatabaseEx
     * @expectedExceptionMessage MySQL driver currently supports only SQL fixture format.
     */
    public function dbLoadFixtureNotSupportedFormat()
    {
        // When
        $fixture = '{"key1": "val1"}';

        // Then
        $this->driver->dbLoadFixture(DbItf::FIXTURE_FORMAT_JSON, $fixture);
    }

    /**
     * @test
     *
     * @dataProvider runQueryProvider
     *
     * @covers ::dbRunQuery
     *
     * @param string $sql
     * @param string $expMsg
     */
    public function runQuery($sql, $expMsg)
    {
        // Given
        $resp = null;

        // When
        try {
            $resp = $this->driver->dbRunQuery($sql);
            $thrown = false;
            $gotMsg = '';
        } catch (DatabaseEx $e) {
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
     * @test
     *
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

