<?php

/** Copyright 2016 ITHGroup. */

namespace Kicaj\Test\Helper\Server;

/**
 * HttpServerTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Server\HttpServer
 *
 * @author Rafal Zajac <rzajac@gmail.com>
 */
class HttpServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Server document root.
     *
     * @var string
     */
    private $docRoot;

    protected function setUp()
    {
        $this->docRoot = getFixturesRootPath() . '/docRoot';
    }

    /**
     * @test
     *
     * @covers ::start
     * @covers ::__construct
     */
    public function construct()
    {
        // Given
        $srv = new HttpServer($this->docRoot, 1111, '127.0.0.1');

        // When
        $pid = $srv->start();

        // Then
        $this->assertTrue(ctype_digit($pid));
        $this->assertSame('Build-in HTTP server works.', file_get_contents($srv->getUrl() . '/test.php'));
    }

    /**
     * @test
     *
     * @dataProvider getURLProvider
     *
     * @covers ::getUrl
     *
     * @param string $docRoot
     * @param int    $port
     * @param string $host
     * @param string $expected
     */
    public function getURL($docRoot, $port, $host, $expected)
    {
        // When
        $srv = new HttpServer($docRoot, $port, $host);

        // Then
        $this->assertSame($expected, $srv->getUrl());
    }

    public function getURLProvider()
    {
        return [
            ['/path', 9706, '127.0.0.1', 'http://127.0.0.1:9706'],
            ['/path', 9706, '127.0.0.1', 'http://127.0.0.1:9706'],
            ['/path', 9706, '192.168.0.10', 'http://192.168.0.10:9706'],
            ['/path', 9706, 'localhost', 'http://localhost:9706'],
        ];
    }

    /**
     * @test
     *
     * @covers ::start
     * @covers ::stop
     */
    public function stop()
    {
        // Given
        $srv = new HttpServer($this->docRoot, 1111, '127.0.0.1');

        // When
        $srv->start();
        $srv->stop();

        // Then
        $this->assertSame(false, @file_get_contents($srv->getUrl() . '/test.php'));
    }

    /**
     * @test
     *
     * @covers ::start
     * @covers ::stop
     */
    public function stopTwice()
    {
        // Given
        $srv = new HttpServer($this->docRoot, 1111, '127.0.0.1');

        // When
        $srv->start();
        $srv->stop();
        $srv->stop();

        // Then
        $this->assertSame(false, @file_get_contents($srv->getUrl() . '/test.php'));
    }

}
