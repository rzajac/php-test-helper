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
namespace Kicaj\Test\TestHelperTest\Loader {

    use Kicaj\Test\Helper\Loader\_WhatJsonLastError;
    use Kicaj\Test\Helper\Loader\FixtureLoader;
    use Kicaj\Test\Helper\Loader\FixtureLoaderEx;
    use Kicaj\Test\Helper\TestCase\FixtureTestCase;
    use PHPUnit\Framework\TestCase;


    /**
     * FixtureLoaderDecodeTest.
     *
     * @coversDefaultClass \Kicaj\Test\Helper\Loader\FixtureLoader
     */
    class FixtureLoaderDecodeTest extends TestCase
    {
        protected function tearDown()
        {
            _WhatJsonLastError::$jsonLastErrorReturn = _WhatJsonLastError::USE_DEFAULT;
        }

        /**
         * @test
         *
         * @dataProvider decodeProvider
         *
         * @covers ::decode
         *
         * @param string $json
         * @param bool   $asClass
         * @param int    $depth
         * @param string $expErrMsg
         * @param int    $expErrCode
         */
        public function decode($json, $asClass, $depth, $expErrMsg, $expErrCode)
        {
            // Given
            $fixtureLoader = new FixtureLoader(FixtureTestCase::getFixturesRootPath());

            // When
            try {
                $gotErrMsg = '';
                $gotErrCode = '';
                $fixtureLoader->decode($json, $asClass, $depth);
            } catch (FixtureLoaderEx $e) {
                $gotErrMsg = $e->getMessage();
                $gotErrCode = $e->getCode();
            }

            // Then
            $this->assertSame($expErrMsg, $gotErrMsg);
            $this->assertSame($expErrCode, $gotErrCode);
        }

        public function decodeProvider()
        {
            return [
                ['{"aaa": 1}', false, 512, '', ''], // 0
                ['{"aaa: 1}', false, 512, 'JSON decoding error', 3], // 1
                ['{"aaa": {"aaa": {"aaa": {}}}', false, 1, 'Maximum stack depth exceeded', 1], // 2
            ];
        }

        /**
         * @test
         *
         * @covers ::decode
         *
         * @expectedException \Kicaj\Test\Helper\Loader\FixtureLoaderEx
         * @expectedExceptionMessage JSON decoding error
         */
        public function decodeError()
        {
            // Given
            $fixtureLoader = new FixtureLoader(FixtureTestCase::getFixturesRootPath());

            // Then
            $fixtureLoader->decode('{"j": 1 ] }');
        }

        /**
         * @test
         *
         * @covers ::decode
         *
         * @expectedException \Kicaj\Test\Helper\Loader\FixtureLoaderEx
         * @expectedExceptionMessage Unknown error
         */
        public function decodeUnknownError()
        {
            // Given
            $fixtureLoader = new FixtureLoader(FixtureTestCase::getFixturesRootPath());
            _WhatJsonLastError::$jsonLastErrorReturn = 10000;

            // Then
            $json = '{"does not matter what it is here": 1}';
            $fixtureLoader->decode($json);
        }
    }
}

// Trick to inject our own json_last_error() to Kicaj\Test\Helper\Loader namespace.

namespace Kicaj\Test\Helper\Loader {

    class _WhatJsonLastError
    {
        const USE_DEFAULT = -1;

        public static $jsonLastErrorReturn = self::USE_DEFAULT;
    }

    function json_last_error()
    {
        if (_WhatJsonLastError::$jsonLastErrorReturn === _WhatJsonLastError::USE_DEFAULT) {
            return \json_last_error();
        } else {
            return _WhatJsonLastError::$jsonLastErrorReturn;
        }
    }
}
