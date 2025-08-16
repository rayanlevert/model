<?php

namespace RayanLevert\Model\Queries;

use PDO;

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
