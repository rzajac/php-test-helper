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
namespace Kicaj\Test\Helper\TestCase;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Base test case.
 *
 * Base class for all unit tests.
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Get object property even if it's private or protected.
     *
     * @param object $obj      The object to get property value from.
     * @param string $propName The property name.
     *
     * @return mixed
     */
    public static function objectGetProperty($obj, $propName)
    {
        $reflection = new ReflectionClass($obj);
        $reflectionProperty = $reflection->getProperty($propName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($obj);
    }

    /**
     * Set object property even if it's private or protected.
     *
     * @param object $obj      The object to get property value from.
     * @param string $propName The property name.
     * @param mixed  $value    The value to set.
     */
    public static function objectSetProperty($obj, $propName, $value)
    {
        $reflection = new ReflectionClass($obj);
        $reflectionProperty = $reflection->getProperty($propName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($obj, $value);
    }
}
