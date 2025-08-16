<?php

namespace RayanLevert\Model;

use PDO;

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
     * @return string The query to update a record in the database
     */
    // abstract public function update(): string;

    /**
     * Generate a query to delete a record in the database
     *
     * @return string The query to delete a record in the database
     */
    // abstract public function delete(): string;
}