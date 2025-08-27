<?php

namespace RayanLevert\Model;

use PDO;
use RayanLevert\Model\Queries\Statements\Update;

/** Abstract class for generating queries for a specific model */
abstract class Queries
{
    public function __construct(protected readonly Model $model)
    {
    }

    /**
     * Generate a query to create a new record in the database
     *
     * @return string The query to create a new record in the database
     *
     * @throws Exception If the query cannot be generated
     */
    abstract public function create(PDO $pdo): string;

    /**
     * Generate a query to update a record in the database
     *
     * @return Statements\Update The query to update a record in the database with possible placeholders
     */
    abstract public function update(PDO $pdo): Update;

    /**
     * Generate a query to delete a record in the database
     *
     * @return string The query to delete a record in the database
     */
    // abstract public function delete(): string;
}