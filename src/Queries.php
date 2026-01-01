<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Queries\Statement;

/** Interface for generating queries for a specific model */
interface Queries
{
    /**
     * Generate a query to create a new record in the database
     *
     * @return Statement The query to create a new record in the database with the columns values as placeholders
     *
     * @throws Exception If the query cannot be generated
     */
    public function create(Model $model): Statement;

    /**
     * Generate a query to update a record in the database
     *
     * @throws Exception If the query cannot be generated (no columns)
     *
     * @return Statement The query to update a record in the database with the columns values as placeholders
     */
    public function update(Model $model): Statement;

    /**
     * Generate a query to delete a record in the database
     *
     * @throws Exception If the query cannot be generated (no primary key)
     *
     * @return Statement The query to delete a record in the database with the primary key value as placeholder
     */
    public function delete(Model $model): Statement;

    /**
     * Generate a query to select a record by its primary key
     *
     * @param Model $model The model to generate the query for
     * @param int|string $value The value of the primary key to select by
     *
     * @throws Exception If the query cannot be generated (no primary key)
     *
     * @return Statement The query to select a record by its primary key with the primary key value as placeholder
     */
    public function selectByPrimaryKey(Model $model, int|string $value): Statement;

    /**
     * Generate a query to select a record by the given columns
     *
     * @param Model $model The model to generate the query for
     * @param array<string, mixed> $columns The columns and their values to search by
     *
     * @throws Exception If the query cannot be generated (no columns)
     *
     * @return Statement The query to select a record by the given columns with the columns values as placeholders
     */
    public function selectByColumns(Model $model, array $columns): Statement;
}