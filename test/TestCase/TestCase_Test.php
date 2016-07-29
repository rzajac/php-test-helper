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

namespace Kicaj\Test\TestHelperTest\TestCase;

use Kicaj\Test\Helper\TestCase\TestCase;

/**
 * FixtureTestCase_Test.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\TestCase\TestCase
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class TestCase_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HelperPropClass
     */
    protected $helperClass;

    protected function setUp()
    {
        $this->helperClass = new HelperPropClass();

        // Ensure start values.
        $this->assertNull($this->helperClass->getProt());
        $this->assertNull($this->helperClass->getPriv());
    }

    /**
     * @covers ::objectGetProperty
     */
    public function test_objectGetProperty_protected()
    {
        // When
        $this->helperClass->setProt('abc');

        // Then
        $this->assertSame('abc', TestCase::objectGetProperty($this->helperClass, 'prot'));
    }

    /**
     * @covers ::objectGetProperty
     */
    public function test_objectGetProperty_private()
    {
        // When
        $this->helperClass->setPriv('def');

        // Then
        $this->assertSame('def', TestCase::objectGetProperty($this->helperClass, 'priv'));
    }

    /**
     * @covers ::objectSetProperty
     */
    public function test_objectSetProperty_protected()
    {
        // When
        TestCase::objectSetProperty($this->helperClass, 'prot', 123);

        // Then
        $this->assertSame(123, $this->helperClass->getProt());
    }

    /**
     * @covers ::objectSetProperty
     */
    public function test_objectSetProperty_private()
    {
        // When
        TestCase::objectSetProperty($this->helperClass, 'priv', 456);

        // Then
        $this->assertSame(456, $this->helperClass->getPriv());
    }
}


class HelperPropClass
{
    protected $prot;
    private $priv;

    /**
     * @return mixed
     */
    public function getProt()
    {
        return $this->prot;
    }

    /**
     * @param mixed $prot
     *
     * @return HelperPropClass
     */
    public function setProt($prot)
    {
        $this->prot = $prot;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriv()
    {
        return $this->priv;
    }

    /**
     * @param mixed $priv
     *
     * @return HelperPropClass
     */
    public function setPriv($priv)
    {
        $this->priv = $priv;

        return $this;
    }
}
