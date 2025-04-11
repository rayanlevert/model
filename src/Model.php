<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Exceptions\ValidationException;

abstract class Model
{
    public protected(set) State $state = State::TRANSIANT;

    /** @var string The database table name */
    abstract public string $table { get; }

    final public function __construct()
    {
    }

    /**
     * Updates the instance to the database
     *
     * @throws Exception If the instance is not persistent yet
     */
    public function update(): void
    {
        if (State::TRANSIANT === $this->state) {
            throw new Exception('Cannot update an instance that is not persistent yet');
        }
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
