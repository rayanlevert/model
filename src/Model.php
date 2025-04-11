<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Exceptions\ValidationException;

abstract class Model
{
    /** @var string The database table name */
    abstract public string $table { get; }

    final public function __construct()
    {
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
