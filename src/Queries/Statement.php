<?php

namespace RayanLevert\Model\Queries;

/**
 * Represents a SQL query with the whole SQL query and the values for placeholders (in order)
 */
final readonly class Statement
{
    public array $values;

    public function __construct(
        public string $query,
        mixed ...$values
    ) {
        $this->values = $values;
    }
}