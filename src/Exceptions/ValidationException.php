<?php

namespace RayanLevert\Model\Exceptions;

use function implode;

/** Exception thrown when validation fails */
class ValidationException extends \Exception
{
    /** @var string[] Array of validation error messages */
    private array $errors;

    /** @param string ...$errors Validation error messages */
    public function __construct(string ...$errors)
    {
        $this->errors = $errors;

        parent::__construct(implode(', ', $errors));
    }

    /** @return string[] Array of validation error messages */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
