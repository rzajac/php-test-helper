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

use Kicaj\Test\Helper\Database\DbGet;
use Kicaj\Tools\Db\DbConnector;

/**
 * DbGet tests.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\DbGet
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbGet_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider factoryProvider
     *
     * @covers ::factory
     *
     * @param string $driverName
     * @param string $expErrorMsg
     */
    public function test_factory($driverName, $expErrorMsg)
    {
        $dbConfig           = getUnitTestDbConfig('HELPER1');
        $dbConfig['driver'] = $driverName;

        $mysql = null;

        try {
            $mysql       = DbGet::factory($dbConfig);
            $gotErrorMsg = '';
        } catch (\Exception $e) {
            $gotErrorMsg = $e->getMessage();
        }

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
     * @covers ::factory
     */
    public function test_factory_sameInstance()
    {
        $db1 = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $db2 = DbGet::factory(getUnitTestDbConfig('HELPER1'));

        $this->assertSame($db1, $db2);
    }

    /**
     * @covers ::factory
     */
    public function test_factory_notSameInstance()
    {
        $db1 = DbGet::factory(getUnitTestDbConfig('HELPER1'));
        $db2 = DbGet::factory(getUnitTestDbConfig('HELPER2'));

        $this->assertNotSame($db1, $db2);
    }
}
