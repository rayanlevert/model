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
    protected PDO $pdo;

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
}
