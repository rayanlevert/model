<?php

namespace RayanLevert\Model\Tests;

use PDOException;
use RayanLevert\Model\Connection;
use RayanLevert\Model\Connections\Mysql;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Exception;
use ReflectionProperty;

class DataObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testPdoException(): void
    {
        $oC = new class('test-host') extends Connection
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
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->assertSame('mysql', $oDataObject->getDriverName());
    }

    public function testCommitNoTransaction(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no active transaction');

        $oDataObject->commit();
    }

    public function testStartTransaction(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
    }

    public function testStartTransactionWithCommitBefore(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A transaction is already active, cannot start a new one');

        $oDataObject->startTransaction();
    }

    public function testCommitOk(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
        $oDataObject->commit();
    }

    /**
     * Returns a working DataObject to the mysql database
     */
    private function getConnectionClass(): Mysql
    {
        return new Mysql('percona', 'root', 'root-password');
    }
}
