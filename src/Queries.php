<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Queries\Statement;

/** Abstract class for generating queries for a specific model */
abstract class Queries
{
    /**
     * Generate a query to create a new record in the database
     *
     * @return Statement The query to create a new record in the database with placeholders
     *
     * @throws Exception If the query cannot be generated
     */
    abstract public function create(Model $model): Statement;

    /**
     * Generate a query to update a record in the database
     *
     * @throws Exception If the query cannot be generated (no columns)
     *
     * @return Statement The query to update a record in the database with possible placeholders
     */
    abstract public function update(Model $model): Statement;

    /**
     * Generate a query to delete a record in the database
     *
     * @throws Exception If the query cannot be generated (no primary key)
     *
     * @return Statement The query to delete a record in the database with the primary key placeholder
     */
    abstract public function delete(Model $model): Statement;
}