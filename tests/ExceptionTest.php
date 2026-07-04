<?php

namespace RayanLevert\Model\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Exception;

#[CoversClass(Exception::class)]
class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function construct(): void
    {
        $e = new Exception('test', 123);

        $this->assertSame('test', $e->getMessage());
        $this->assertSame(123, $e->realCode);
        $this->assertSame(123, $e->getCode());
        $this->assertSame(null, $e->getPrevious());

        // Example PDO SQLSTATE error code for "Column not found"
        $e = new Exception('test', '42S22');

        $this->assertSame('test', $e->getMessage());
        $this->assertSame('42S22', $e->realCode);
        $this->assertSame((int) '42S22', $e->getCode());
        $this->assertSame(null, $e->getPrevious());
    }
}
