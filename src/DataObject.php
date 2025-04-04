<?php

namespace RayanLevert\Model;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Abstraction layer to access to a database through PHP's Data Objects (PDO)
 */
class DataObject
{
    /** Backed property handling the PDO instance */
    private private(set) ?PDO $backedPDO = null;

    /**
     * Virtual property representing the PHP's PDO instance
     *
     * @throws Exception If the database connection has been closed beforehand
     */
    public PDO $pdo {
        get {
            if (!$this->backedPDO) {
                throw new Exception('Connection to the database has been closed, no PDO is available');
            }

            return $this->backedPDO;
        }
    }

    /** PDO's driver name */
    public string $driverName {
        get => $this->backedPDO?->getAttribute(PDO::ATTR_DRIVER_NAME) ?: '';
    }

    /**
     * Initialises the connection to the database
     *
     * @param Connection $connection Database-specific parameters used to connect
     *
     * @throws PDOException If the attempt to connect to the requested database fails
     */
    public function __construct(protected readonly Connection $connection)
    {
        $this->start();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Restart the connection to the database with the same parameters passed to the constructor
     *
     * @throws PDOException If the attempt to connect to the requested database fails
     */
    public function start(): void
    {
        $this->backedPDO = PDO::connect(...$this->connection->getPDOParameters());
    }

    /** Closes PDO connection (closes it as well when PHP destructs the object) */
    public function close(): void
    {
        $this->backedPDO = null;
    }

    /**
     * Starts a transaction
     *
     * @throws Exception If the database is already in transaction or does not support it
     */
    public function startTransaction(): void
    {
        if (!$this->backedPDO) {
            throw new Exception('The connection to database has been closed, no transaction can be started');
        }

        if ($this->backedPDO->inTransaction()) {
            throw new Exception('A transaction is already active, cannot start a new one');
        }

        try {
            $this->backedPDO->beginTransaction();
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
        if (!$this->backedPDO?->inTransaction()) {
            throw new Exception('There is no active transaction');
        }

        $this->backedPDO->commit();
    }

    /**
     * Rolls back a transaction and puts back the database in autocommit mode
     *
     * @throws Exception If there is no active transaction
     */
    public function rollback(): void
    {
        if (!$this->backedPDO?->inTransaction()) {
            throw new Exception('There is no active transaction to rollback');
        }

        try {
            $this->backedPDO->rollBack();
        } catch (PDOException $e) {
            throw new Exception('Failed to rollback transaction: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks if the connection is active
     *
     * @return bool True if the connection is active, false otherwise
     */
    public function isConnected(): bool
    {
        return $this->backedPDO !== null;
    }
}
