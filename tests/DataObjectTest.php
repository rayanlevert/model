<?php

namespace RayanLevert\Model\Tests;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connection;
use RayanLevert\Model\Connections\Mysql;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Exception;
use ReflectionProperty;

#[CoversClass(DataObject::class)]
class DataObjectTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function pdoException(): void
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

    #[Test]
    public function connectionOk(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->assertSame('mysql', $oDataObject->driverName);
    }

    #[Test]
    public function commitNoTransaction(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no active transaction');

        $oDataObject->commit();
    }

    #[Test]
    public function startTransaction(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
    }

    #[Test]
    public function startTransactionWithCommitBefore(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A transaction is already active, cannot start a new one');

        $oDataObject->startTransaction();
    }

    #[Test]
    public function startTransactionWithPdoNull(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The connection to database has been closed, no transaction can be started');

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->close();
        $oDataObject->startTransaction();
    }

    #[Test]
    public function commitOk(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
        $oDataObject->commit();
    }

    #[Test]
    public function destruct(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->__destruct();

        $this->expectExceptionMessage('Connection to the database has been closed, no PDO is available');

        $oDataObject->pdo;
    }

    #[Test]
    public function close(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->close();

        $this->expectExceptionMessage('Connection to the database has been closed, no PDO is available');

        $oDataObject->pdo;
    }

    #[Test]
    public function start(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $oPdo = new ReflectionProperty($oDataObject, 'pdo')->getValue($oDataObject);

        $oDataObject->start();

        $this->assertNotSame(
            $oPdo,
            new ReflectionProperty($oDataObject, 'pdo')->getValue($oDataObject)
        );
    }

    #[Test]
    public function getPdo(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->assertInstanceOf(PDO::class, $oDataObject->pdo);

        $this->expectExceptionMessage('Connection to the database has been closed, no PDO is available');

        $oDataObject->close() || $oDataObject->pdo;
    }

    #[Test]
    public function rollbackNoTransaction(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no active transaction to rollback');

        $oDataObject->rollback();
    }

    #[Test]
    public function rollbackOk(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
        $oDataObject->rollback();
    }

    #[Test]
    public function isConnectedWhenConnected(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->assertTrue($oDataObject->isConnected());
    }

    #[Test]
    public function isConnectedWhenDisconnected(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->close();

        $this->assertFalse($oDataObject->isConnected());
    }

    #[Test]
    public function testStartTransactionThrowsExceptionWhenPDOThrowsException(): void
    {
        $mockPDO = $this->createMock(PDO::class);
        $mockPDO->expects($this->once())
            ->method('beginTransaction')
            ->willThrowException(new PDOException('Test exception'));

        $dataObject = new class($this->createMock(Connection::class)) extends DataObject {
            public function setBackedPDO(?PDO $pdo): void
            {
                $this->backedPDO = $pdo;
            }

            public function start(): void {}
        };
        $dataObject->setBackedPDO($mockPDO);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $dataObject->startTransaction();
    }

    #[Test]
    public function testRollbackThrowsExceptionWhenPDOThrowsException(): void
    {
        $mockPDO = $this->createMock(PDO::class);
        $mockPDO->expects($this->once())
            ->method('rollBack')
            ->willThrowException(new PDOException('Test exception'));

        $mockPDO->expects($this->once())
            ->method('inTransaction')
            ->willReturn(true);

        $dataObject = new class($this->createMock(Connection::class)) extends DataObject {
            public function setBackedPDO(?PDO $pdo): void
            {
                $this->backedPDO = $pdo;
            }

            public function start(): void {}
        };
        $dataObject->setBackedPDO($mockPDO);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        $dataObject->rollback();
    }

    /** Returns a working DataObject to the mysql database */
    private function getConnectionClass(): Mysql
    {
        return new Mysql('percona', 'root', 'root-password');
    }
}
