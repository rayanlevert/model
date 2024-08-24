<?php

namespace RayanLevert\Model;

use PDO;
use PDOException;

/**
 * Abstraction layer to access to a database through PHP's Data Objects (PDO)
 */
class DataObject
{
    /** PHP's PDO instance */
    protected ?PDO $pdo = null;

    /**
     * Initialises the connection to the database
     *
     * @param Connection $connection Database-specific parameters used to connect
     *
     * @throws PDOException If the attempt to connect to the requested database fails
     */
    public function __construct(protected readonly Connection $connection)
    {
        $this->pdo = new PDO(...$connection->getPDOParameters());
    }

    /** Returns PDO's driver name */
    public function getDriverName(): string
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Starts a transaction
     *
     * @throws Exception If the database is already in transaction or does not support it
     */
    public function startTransaction(): void
    {
        if ($this->pdo->inTransaction()) {
            throw new Exception('A transaction is already active, cannot start a new one');
        }

        try {
            $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            throw new Exception('Cannot start a transaction, message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Commits a transaction and puts back the database in autocommit mode
     *
     * @throws Exception If there is no active transaction
     */
    public function commit(): void
    {
        if (!$this->pdo->inTransaction()) {
            throw new Exception('There is no active transaction');
        }

        $this->pdo->commit();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}
