<?php

namespace RayanLevert\Model\Tests;

use RayanLevert\Model\Connections\Mysql as ConnectionsMysql;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Model;
use RayanLevert\Model\Queries\Mysql;

/** Test case where actual databases queries (insert, update, delete) are performed */
abstract class TransactionTestCase extends \PHPUnit\Framework\TestCase
{
    protected DataObject $dataObject;

    protected function setUp(): void
    {
        $this->dataObject = new DataObject($this->getConnection(), new Mysql());
        $this->dataObject->startTransaction();

        Model::$dataObject = $this->dataObject;
    }

    protected function tearDown(): void
    {
        $this->dataObject->rollback();

        Model::$dataObject = null;
    }

    /** Returns a working DataObject to the mysql database */
    protected function getConnection(): ConnectionsMysql
    {
        return new ConnectionsMysql('percona', 'root', 'root-password', ['dbname' => 'model']);
    }
}
