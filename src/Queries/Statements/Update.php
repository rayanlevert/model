<?php

namespace RayanLevert\Model\Queries\Statements;

/**
 * Represents an UPDATE query with the whole SQL query and the values for placeholders (in order)
 */
readonly class Update
{
    public array $values;

    public function __construct(
        public string $query,
        mixed ...$values
    ) {
        $this->values = $values;
    }
}