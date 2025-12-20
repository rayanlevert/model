<?php

namespace RayanLevert\Model\Queries;

use RayanLevert\Model\Exception;
use RayanLevert\Model\Model;

use function implode;
use function array_map;
use function array_keys;
use function array_values;
use function array_fill;
use function count;

/** Queries for MySQL databases */
class Mysql extends \RayanLevert\Model\Queries
{
    public function create(Model $model): Statement
    {
        $aColumns     = $model->columns();
        $columns      = implode(', ', array_map(fn(string $column) => "`$column`", array_keys($aColumns)));
        $placeholders = implode(', ', array_fill(0, count($aColumns), '?'));

        return new Statement(
            "INSERT INTO `{$model->table}` ($columns) VALUES ($placeholders)",
            ...array_values($aColumns)
        );
    }

    public function update(Model $model): Statement
    {
        $oPrimaryKey = $model->getPrimaryKey();

        $aColumns = $model->columns();
        unset($aColumns[$oPrimaryKey->column]);

        if (!$aColumns) {
            throw new Exception('No columns found in ' . $model::class);
        }

        $columns = implode(', ', array_map(fn(string $column) => "`$column` = ?", array_keys($aColumns)));

        return new Statement(
            "UPDATE `{$model->table}` SET $columns WHERE `{$oPrimaryKey->column}` = ?",
            ...[...array_values($aColumns), $oPrimaryKey->value]
        );
    }

    public function delete(Model $model): Statement
    {
        $oPrimaryKey = $model->getPrimaryKey();

        return new Statement("DELETE FROM `{$model->table}` WHERE `{$oPrimaryKey->column}` = ?", $oPrimaryKey->value);
    }
}
