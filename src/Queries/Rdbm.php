<?php

namespace RayanLevert\Model\Queries;

use RayanLevert\Model\Exception;
use RayanLevert\Model\Model;

/**
 * Abstract class for multiple RDMBs which have the same SQL code for generating queries
 *
 * - Sqlite
 * - Mysql
 * - Postgres
 */
abstract class Rdbm implements \RayanLevert\Model\Queries
{
    public function create(Model $model): Statement
    {
        $aColumns = $model->columns();
        $columns  = implode(
            ', ',
            array_map(
                fn (string $column) => sprintf("%1\$s$column%1\$s", $this->escapeCharacter),
                array_keys($aColumns)
            )
        );

        $placeholders = implode(', ', array_fill(0, count($aColumns), '?'));

        return new Statement(
            sprintf(
                "INSERT INTO %1\$s{$model->table}%1\$s ($columns) VALUES ($placeholders)",
                $this->escapeCharacter
            ),
            ...array_values($aColumns)
        );
    }

    public function update(Model $model): Statement
    {
        $oPrimaryKey = $model->getPrimaryKey();
        $aColumns     = $model->columns();

        unset($aColumns[$oPrimaryKey->column]);

        if (!$aColumns) {
            throw new \RayanLevert\Model\Exception('No columns found in ' . $model::class);
        }

        $columns = implode(
            ', ',
            array_map(
                fn (string $column) => sprintf("%1\$s$column%1\$s = ?", $this->escapeCharacter),
                array_keys($aColumns)
            )
        );

        return new Statement(
            sprintf(
                "UPDATE %1\$s{$model->table}%1\$s SET $columns WHERE %1\$s{$oPrimaryKey->column}%1\$s = ?",
                $this->escapeCharacter
            ),
            ...[...array_values($aColumns), $oPrimaryKey->value]
        );
    }

    public function delete(Model $model): Statement
    {
        $oPrimaryKey = $model->getPrimaryKey();

        return new Statement(
            sprintf(
                "DELETE FROM %1\$s{$model->table}%1\$s WHERE %1\$s{$oPrimaryKey->column}%1\$s = ?",
                $this->escapeCharacter
            ),
            $oPrimaryKey->value
        );
    }

    public function selectByPrimaryKey(Model $model, int|string $value): Statement
    {
        $oPrimaryKey = $model->getPrimaryKey();

        return new Statement(
            sprintf(
                "SELECT * FROM %1\$s{$model->table}%1\$s WHERE %1\$s{$oPrimaryKey->column}%1\$s = ?",
                $this->escapeCharacter
            ),
            $value
        );
    }

    public function selectByColumns(Model $model, array $columns): Statement
    {
        if (!$columns) {
            throw new Exception('No columns found in ' . $model::class);
        }

        $aColumns = array_map(
            fn (string $column) => sprintf("%1\$s{$model->getDatabaseColumnName($column)}%1\$s = ?", $this->escapeCharacter),
            array_keys($columns)
        );

        $placeholders = implode(' AND ', $aColumns);

        return new Statement(
            sprintf("SELECT * FROM %1\$s{$model->table}%1\$s WHERE $placeholders", $this->escapeCharacter),
            ...array_values($columns)
        );
    }
}
