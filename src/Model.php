<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Exceptions\ValidationException;

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

    /**
     * Validate all properties with Validation attributes
     * 
     * @throws ValidationException If any validation fails
     */
    public function validate(): void
    {
        Validation::validateProperties($this);
    }
}
