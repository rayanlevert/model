<?php

namespace RayanLevert\Model\Queries;

use PDO;
use RayanLevert\Model\Exception;

use function implode;
use function array_map;
use function array_keys;
use function is_string;
use function is_null;
use function is_bool;

/** Queries for MySQL databases */
class Mysql extends \RayanLevert\Model\Queries
{
    public function create(PDO $pdo): string
    {
        $aColumns = $this->model->columns();
        $columns  = implode(', ', array_map(fn(string $column) => "`$column`", array_keys($aColumns)));
        $values   = implode(', ', array_map(fn(mixed $value) => self::quote($pdo, $value), $aColumns));

        return "INSERT INTO `{$this->model->table}` ($columns) VALUES ($values)";
    }

    public function update(PDO $pdo): Statement
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

    public function delete(PDO $pdo): Statement
    {
        $oPrimaryKey = $this->model->getPrimaryKey();

        return new Statement(
            "DELETE FROM `{$this->model->table}` WHERE `{$oPrimaryKey->column}` = ?",
            $oPrimaryKey->value
        );
    }

    /** Transforms a value to a string to be used in a query */
    private static function quote(PDO $pdo, mixed $value): string
    {
        return match (true) {
            is_string($value) => $pdo->quote($value),
            is_null($value)   => 'NULL',
            is_bool($value)   => (int) $value,
            default           => $value
        };
    }
}
