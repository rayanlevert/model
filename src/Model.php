<?php

namespace RayanLevert\Model;

abstract class Model
{
    /** @var string The database table name */
    protected readonly string $table;

    /** Table name for the model */
    protected abstract function table(): string;

    final public function __construct()
    {
        $this->table = $this->table();
    }
    
    /** Returns the table name */
    final public function getTable(): string
    {
        return $this->table;
    }
}
