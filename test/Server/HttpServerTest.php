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
     * @covers ::getPort
     */
    public function construct()
    {
        // Given
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);

        // When
        $pid = $srv->start();

        // Then
        $this->assertTrue(ctype_digit($pid));
        $this->assertSame(1111, $srv->getPort());
        $this->assertRegExp('/.*Build-in HTTP server works\..*/', file_get_contents($srv->getUrl() . '/test.php'));
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
            ['/path', '127.0.0.1', 9706, 'http://127.0.0.1:9706'],
            ['/path', '127.0.0.1', 9706, 'http://127.0.0.1:9706'],
            ['/path', '192.168.0.10', 9706, 'http://192.168.0.10:9706'],
            ['/path', 'localhost', 9706, 'http://localhost:9706'],
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
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);

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
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);

        // When
        $srv->start();
        $srv->stop();
        $srv->stop();

        // Then
        $this->assertSame(false, @file_get_contents($srv->getUrl() . '/test.php'));
    }

    /**
     * @test
     *
     * @dataProvider setDirectivesProvider
     *
     * @covers ::setDirectives
     *
     * @param array  $directives
     * @param string $expected
     */
    public function setDirectives($directives, $expected)
    {
        // Given
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);

        // When
        $got = $srv->setDirectives($directives);

        // Then
        $this->assertSame($expected, $got);
    }

    public function setDirectivesProvider()
    {
        return [
            [['session.save_path' => '/tmp', 'directive' => null], '-d session.save_path=/tmp -d directive'],
        ];
    }

    /**
     * @test
     *
     * @covers ::setIniPath
     */
    public function setIniPath()
    {
        // Given
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);

        // When
        $path = $srv->setIniPath('/some/path');

        // Then
        $this->assertSame('-c /some/path', $path);
    }

    /**
     * @test
     *
     * @covers ::getStartCmd
     */
    public function getStartCmd()
    {
        // Given
        $srv = new HttpServer($this->docRoot, '127.0.0.1', 1111);
        $srv->setIniPath('/some/path');
        $srv->setDirectives(['session.save_path' => '/tmp', 'something_empty' => null]);

        // When
        $cmd = $srv->getStartCmd();
        $srv->start();

        // Then
        $this->assertSame('php -S 127.0.0.1:1111 -t test/fixtures/docRoot  -d session.save_path=/tmp -d something_empty >/dev/null 2>&1 & echo $!', $cmd);
        $this->assertSame("Build-in HTTP server works.\nsession.save_path=/tmp", file_get_contents($srv->getUrl() . '/test.php'));
    }
}
