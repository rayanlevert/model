<?php

namespace RayanLevert\Model\Tests;

use PDO;
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

    public function testStartTransactionWithPdoNull(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The connection to database has been closed, no transaction can be started');

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->close();
        $oDataObject->startTransaction();
    }

    public function testCommitOk(): void
    {
        $this->expectNotToPerformAssertions();

        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->startTransaction();
        $oDataObject->commit();
    }

    public function testDestruct(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->__destruct();

        $oPdo = (new ReflectionProperty($oDataObject, 'pdo'))->getValue($oDataObject);

        $this->assertNull($oPdo);
    }

    public function testClose(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());
        $oDataObject->close();

        $oPdo = (new ReflectionProperty($oDataObject, 'pdo'))->getValue($oDataObject);

        $this->assertNull($oPdo);
    }

    public function testStart(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $oPdo = (new ReflectionProperty($oDataObject, 'pdo'))->getValue($oDataObject);

        $oDataObject->start();

        $this->assertNotSame(
            $oPdo,
            (new ReflectionProperty($oDataObject, 'pdo'))->getValue($oDataObject)
        );
    }

    public function testGetPdo(): void
    {
        $oDataObject = new DataObject($this->getConnectionClass());

        $this->assertInstanceOf(PDO::class, $oDataObject->getPDO());

        $this->expectExceptionMessage('Connection to the database has been closed, no PDO is available');

        $oDataObject->close() || $oDataObject->getPDO();
    }

    /**
     * Returns a working DataObject to the mysql database
     */
    private function getConnectionClass(): Mysql
    {
        return new Mysql('percona', 'root', 'root-password');
    }
}
