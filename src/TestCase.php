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
namespace Kicaj\Test\Helper;

use Exception;
use Kicaj\Test\Helper\Database\TestDb;
use Kicaj\Test\Helper\Database\DbGet;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * Base test case.
 *
 * Base class for all unit tests.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
}
