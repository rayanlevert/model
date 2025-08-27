<?php

namespace RayanLevert\Model\Queries;

use RayanLevert\Model\Exception;

use function implode;
use function array_map;
use function array_keys;
use function array_values;
use function array_fill;
use function count;

/** Queries for MySQL databases */
class Mysql extends \RayanLevert\Model\Queries
{
    public function create(): Statement
    {
        $aColumns     = $this->model->columns();
        $columns      = implode(', ', array_map(fn(string $column) => "`$column`", array_keys($aColumns)));
        $placeholders = implode(', ', array_fill(0, count($aColumns), '?'));

        return new Statement(
            "INSERT INTO `{$this->model->table}` ($columns) VALUES ($placeholders)",
            ...array_values($aColumns)
        );
    }

    public function update(): Statement
    {
        $oPrimaryKey = $this->model->getPrimaryKey();

        $aColumns = $this->model->columns();
        unset($aColumns[$oPrimaryKey->column]);

        if (!$aColumns) {
            throw new Exception('No columns found in ' . $this->model::class);
        }

        $columns = implode(', ', array_map(fn(string $column) => "`$column` = ?", array_keys($aColumns)));

        return new Statement(
            "UPDATE `{$this->model->table}` SET $columns WHERE `{$oPrimaryKey->column}` = ?",
            ...$aColumns + [$oPrimaryKey->column => $oPrimaryKey->value]
        );
    }

    public function delete(): Statement
    {
        $oPrimaryKey = $this->model->getPrimaryKey();

        return new Statement(
            "DELETE FROM `{$this->model->table}` WHERE `{$oPrimaryKey->column}` = ?",
            $oPrimaryKey->value
        );
    }
}
