<?php declare(strict_types=1);


namespace Kicaj\Test\TestHelperTest\Database;

use Kicaj\Test\Helper\Database\DatabaseEx;
use PHPUnit\Framework\TestCase;

/**
 * DatabaseExTest.
 *
 * @coversDefaultClass \Kicaj\Test\Helper\Database\DatabaseEx
 */
class DatabaseExTest extends TestCase
{
    /**
     * @covers ::makeFromException
     */
    public function test_makeFromException()
    {
        // Given
        $e = new \Exception('ex message', 123);

        // When
        $apiEx = DatabaseEx::makeFromException($e);

        // Then
        $this->assertSame('ex message', $apiEx->getMessage());
        $this->assertSame(123, $apiEx->getCode());
    }

    /**
     * @covers ::makeFromException
     */
    public function test_makeFromException_return_if_already_instance_of_self()
    {
        // Given
        $e = new \Exception('ex message', 123);

        // When
        $apiEx = DatabaseEx::makeFromException($e);
        $apiEx2 = DatabaseEx::makeFromException($apiEx);

        // Then
        $this->assertSame($apiEx, $apiEx2);
    }
}