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

namespace Kicaj\Test\TestHelperTest\Database;

use Kicaj\Test\Helper\Database\DatabaseEx;
use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Test\Helper\Database\DbItf;
use PHPUnit\Framework\TestCase;

/**
 * DbGetTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\DbGet
 */
class DbGetTest extends TestCase
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
        } catch (DatabaseEx $e) {
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
            [DbItf::DB_DRIVER_MYSQL, ''],
            ['unknown', 'Unknown database driver name: unknown'],
        ];
    }

    /**
     * @test
     *
     * @covers ::factory
     *
     * @throws DatabaseEx
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
     *
     * @throws DatabaseEx
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
