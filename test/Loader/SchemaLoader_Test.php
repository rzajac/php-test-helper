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
namespace Kicaj\Test\TestHelperTest\Loader;

use Kicaj\Test\Helper\Loader\SchemaLoader;

/**
 * Tests for SchemaLoader class.
 *
 * @coversDefaultClass Kicaj\Test\Helper\Loader\SchemaLoader
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class SchemaLoader_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function test___construct()
    {
        $schemaLoader = new SchemaLoader();

        $this->assertNotNull($schemaLoader);
    }
}
