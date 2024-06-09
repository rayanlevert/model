<?php

namespace RayanLevert\Model\Tests;

use PDOException;
use RayanLevert\Model\Connection;
use RayanLevert\Model\DataObject;
use ReflectionProperty;

class DataObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testPdoException(): void
    {
        $oC = new class ('test-host') extends Connection
        {
            public function dsn(): string
            {
                return 'test';
            }
        };

        $this->expectException(PDOException::class);

        new DataObject($oC);
    }

    public function testConnectionOk(): void
    {
        $oC = new class ('percona', 'root', 'root-password') extends Connection
        {
            public function dsn(): string
            {
                return "mysql:host={$this->host}";
            }
        };

        $oDataObject = new DataObject($oC);

        $this->assertSame('mysql', $oDataObject->getDriverName());
    }
}
