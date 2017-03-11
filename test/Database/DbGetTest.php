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
namespace Kicaj\Test\TestHelperTest\Database;

use Kicaj\DbKit\DatabaseException;
use Kicaj\DbKit\DbConnector;
use Kicaj\Test\Helper\Database\DbGet;

/**
 * DbGetTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\DbGet
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbGetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @dataProvider factoryProvider
     *
     * @covers ::factory
     *
     * @param string $driverName
     * @param string $expErrorMsg
     */
    public function factory($driverName, $expErrorMsg)
    {
        // Given
        $dbConfig = getUnitTestDbConfig('HELPER1');
        $dbConfig['driver'] = $driverName;
        $mysql = null;

        // When
        try {
            $mysql = DbGet::factory($dbConfig);
            $gotErrorMsg = '';
        } catch (DatabaseException $e) {
            $gotErrorMsg = $e->getMessage();
        }

        // Then
        if ($expErrorMsg) {
            $this->assertSame($expErrorMsg, $gotErrorMsg);
            $this->assertNull($mysql);
        } else {
            $this->assertNotNull($mysql);
            $this->assertInstanceOf('\Kicaj\Test\Helper\Database\DbItf', $mysql);
        }
    }

    public function factoryProvider()
    {
        return [
            [DbConnector::DB_DRIVER_MYSQL, ''],
            ['unknown', 'Unknown database driver name: unknown'],
        ];
    }

    /**
     * @test
     *
     * @covers ::factory
     */
    public function factorySameInstance()
    {
        // When
        $db1 = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $db2 = DbGet::factory(getUnitTestDbConfig('HELPER1'));

        // Then
        $this->assertSame($db1, $db2);
    }

    /**
     * @test
     *
     * @covers ::factory
     */
    public function factoryNotSameInstance()
    {
        // When
        $db1 = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $db2 = DbGet::factory(getUnitTestDbConfig('HELPER2'));

        // Then
        $this->assertNotSame($db1, $db2);
    }
}