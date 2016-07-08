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

use Kicaj\Test\Helper\Database\DbItf;
use Kicaj\Test\Helper\Database\Driver\MySQL;
use Kicaj\Test\TestHelperTest\MySQLHelper;
use Kicaj\Tools\Db\DatabaseException;
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

    public function setUp()
    {
        MySQLHelper::resetMySQLDatabases();

        // Connect to default database.
        $this->driver = new MySQL();
        $this->driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();
    }

    protected function tearDown()
    {
        $this->driver->dbClose();
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
     * @param string $expMsg   The expected error message.
     */
    public function test_connection($host, $username, $password, $database, $port, $timezone, $expMsg)
    {
        // Database config
        $dbConfig = [
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $database,
            'port' => $port,
            'timezone' => $timezone
        ];

        $driver = new MySQL();

        try {
            $thrown = false;
            $db     = $driver->dbSetup($dbConfig);

            $this->assertSame($driver, $db);
            $this->assertFalse($driver->isConnected());
            $db = $driver->dbConnect();
            $this->assertSame($driver, $db);
            $this->assertTrue($driver->isConnected());

            $driver->dbGetTableNames(); // Call method that is actually doing something with database.
        } catch (DatabaseException $e) {
            $thrown = true;

            if ($expMsg === '') {
                $this->fail('Did not expect to see error: ' . $e->getMessage());
            }
            $this->assertRegExp($expMsg, $e->getMessage());
        } finally {
            if ($expMsg !== '' && $thrown === false) {
                $this->fail('Expected to see error: ' . $expMsg);
            }
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
                ''
            ],

            [
                $GLOBALS['TEST_DB_HELPER1_HOST'],
                $GLOBALS['TEST_DB_HELPER1_USERNAME'],
                $GLOBALS['TEST_DB_HELPER1_PASSWORD'],
                $GLOBALS['TEST_DB_HELPER1_DATABASE'],
                3306,
                'NOT_EXISTING',
                '/Setting timezone \(NOT_EXISTING\) for MySQL driver failed.*/'
            ],

            [
                $GLOBALS['TEST_DB_HELPER1_HOST'],
                $GLOBALS['TEST_DB_HELPER1_USERNAME'],
                $GLOBALS['TEST_DB_HELPER1_PASSWORD'],
                'not_existing',
                3306,
                'UTC',
                "/Access denied for user .* to database 'not_existing'/"
            ],
        ];
    }

    /**
     * @covers ::dbConnect
     *
     * @expectedException \Kicaj\Tools\Db\DatabaseException
     * @expectedExceptionMessage Setting timezone (UTC) for MySQL driver failed. Please load timezone information using mysql_tzinfo_to_sql.
     */
    public function test_dbConnect_timezoneError()
    {
        $driver              = new MySQL();
        $reflection          = new ReflectionClass($driver);
        $reflection_property = $reflection->getProperty('sqlSetTimezone');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($driver, 'BAD SQL IS ENOUGH');

        $driver->dbSetup(getUnitTestDbConfig('HELPER1'))->dbConnect();
    }

    /**
     * @covers ::dbConnect
     * @covers ::isConnected
     */
    public function test_dbConnect_return_if_connected()
    {
        $this->assertTrue($this->driver->isConnected());
        $driver = $this->driver->dbConnect();

        $this->assertSame($this->driver, $driver);
        $this->assertTrue($this->driver->isConnected());
    }

    /**
     * @covers ::useDatabase
     */
    public function test_useDatabase()
    {
        $driver = $this->driver->useDatabase('testHelper1');

        $this->assertSame($this->driver, $driver);
    }

    /**
     * @covers ::useDatabase
     *
     * @expectedException \Kicaj\Tools\Db\DatabaseException
     * @expectedExceptionMessage __not_existing__
     */
    public function test_useDatabase_error()
    {
        $this->driver->useDatabase('__not_existing__');
    }

    /**
     * @covers ::dbGetTableNames
     */
    public function test_getDbTableNames()
    {
        $tableNames = $this->driver->dbGetTableNames();
        $this->assertSame(['test1', 'test2', 'test3'], $tableNames);
    }

    /**
     * @covers ::dbGetTableNames
     *
     * @expectedException \Kicaj\Tools\Db\DatabaseException
     * @expectedExceptionMessageRegExp /Incorrect database name/
     */
    public function test_getDbTableNames_error()
    {
        // Connect to default database.
        $driver = new MySQL();
        $driver->dbSetup(getUnitTestDbConfig('HELPER_NOT_THERE'))->dbConnect();
        $driver->dbGetTableNames();
    }

    /**
     * @covers ::dbCountTableRows
     *
     * @expectedException \Kicaj\Tools\Db\DatabaseException
     * @expectedExceptionMessageRegExp /Table .* doesn't exist/
     */
    public function test_countTableRows_not_existing_table()
    {
        $this->driver->dbCountTableRows('notExisting');
    }

    /**
     * @covers ::dbCountTableRows
     */
    public function test_countTableRows()
    {
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_countTableRows
     */
    public function test_truncateTables_emptyArray()
    {
        $this->driver->dbTruncateTables([]);

        // No changes.
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_countTableRows
     */
    public function test_truncateTables_array()
    {
        $this->driver->dbTruncateTables(['test2', 'test3']);

        // Test changes visible.
        $this->assertSame(1, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbTruncateTables
     *
     * @depends test_countTableRows
     */
    public function test_truncateTables_string()
    {
        $this->driver->dbTruncateTables('test1');

        // Test changes visible.
        $this->assertSame(0, $this->driver->dbCountTableRows('test1'));
        $this->assertSame(2, $this->driver->dbCountTableRows('test2'));
        $this->assertSame(0, $this->driver->dbCountTableRows('test3'));
    }

    /**
     * @covers ::dbDropTables
     */
    public function test_dropTables()
    {
        $this->driver->dbDropTables(['test1', 'test3']);

        $this->assertSame(1, count($this->driver->dbGetTableNames()));

        $this->driver->dbDropTables('test2');
        $this->assertSame(0, count($this->driver->dbGetTableNames()));
    }

    /**
     * @covers ::dbGetTableData
     */
    public function test_dbGetTableData()
    {
        $got = $this->driver->dbGetTableData('test2');

        $expected = [
            ['id' => '1', 'col2' => '2'],
            ['id' => '2', 'col2' => '22'],
        ];

        $this->assertSame($expected, $got);
    }

    /**
     * @covers ::dbGetTableData
     *
     * @expectedException \Kicaj\Tools\Db\DatabaseException
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
        $fixture = [
            "INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '600')",
            "INSERT INTO `test2` (`id`, `col2`) VALUES (NULL, '700')"
        ];

        $this->driver->dbLoadFixture(DbItf::FIXTURE_FORMAT_SQL, $fixture);

        $got      = $this->driver->dbGetTableData('test2');
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
     * @expectedException \Kicaj\Tools\Db\DatabaseException
     * @expectedExceptionMessage MySQL driver currently supports only SQL fixture format.
     */
    public function test_dbLoadFixture_notSupportedFormat()
    {
        $fixture = '{"key1": "val1"}';
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
        $resp = null;

        try {
            $resp   = $this->driver->dbRunQuery($sql);
            $thrown = false;
            $gotMsg = '';
        } catch (DatabaseException $e) {
            $thrown = true;
            $gotMsg = $e->getMessage();
        }

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
        $this->assertTrue($this->driver->isConnected());

        $this->driver->dbClose();

        $this->assertFalse($this->driver->isConnected());
    }
}
