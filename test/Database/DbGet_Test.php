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
use Kicaj\Tools\Exception;
use Kicaj\Tools\Itf\DbConnect;

/**
 * DbGet tests.
 *
 * @coversDefaultClass Kicaj\Test\Helper\Database\DbGet
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class DbGet_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Database configuration.
     *
     * @var array
     */
    protected $dbConfig = [];

    protected function setUp()
    {
        parent::setUp();

        $this->dbConfig = [
            'driver' => DbConnect::DB_DRIVER_MYSQL,
            'username' => 'root',
            'password' => '',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'test',
        ];
    }

    /**
     * @dataProvider factoryProvider
     *
     * @covers ::factory
     *
     * @param string $driverName
     * @param bool   $throws
     * @param string $errorMsgExp
     */
    public function test_factory($driverName, $throws, $errorMsgExp)
    {
        $this->dbConfig['driver'] = $driverName;

        try {
            $mysql = DbGet::factory($this->dbConfig);

            $hasThrown = false;
            $this->assertSame($throws, $hasThrown);
            $this->assertNotNull($mysql);
            $this->assertInstanceOf('\Kicaj\Test\Helper\Database\TestDb', $mysql);
        } catch (Exception $e) {
            $hasThrown = true;
            $this->assertSame($errorMsgExp, $e->getMessage());
        }

        $this->assertSame($throws, $hasThrown);
    }

    public function factoryProvider()
    {
        return [
            [DbConnect::DB_DRIVER_MYSQL, false, ''],
            ['unknown', true, 'unknown database driver name: unknown'],
        ];
    }
}
