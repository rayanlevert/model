<?php

namespace RayanLevert\Model\Tests;

use RayanLevert\Model\Connection;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Model;
use RayanLevert\Model\Queries;

/** Test case where actual databases queries (insert, update, delete) are performed */
abstract class TransactionTestCase extends \PHPUnit\Framework\TestCase
{
    protected DataObject $dataObject;

    /** Returns a working Connection to the database */
    abstract protected function getConnection(): Connection;

    /** Returns a Queries object to the database */
    abstract protected function getQueries(): Queries;

    protected function setUp(): void
    {
        $this->dataObject = new DataObject($this->getConnection(), $this->getQueries());
        $this->dataObject->startTransaction();

        Model::$dataObject = $this->dataObject;
    }

    protected function tearDown(): void
    {
        $this->dataObject->rollback();

        Model::$dataObject = null;
    }
}
