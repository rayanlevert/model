<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

use function is_string;

/**
 * MinLength attribute for enforcing minimum string length
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     #[MinLength(3)]
 *     private string $username;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength extends Validation
{
    public function __construct(public readonly int $length) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $this->stringFunction('strlen')($value) >= $this->length;
    }

    public function getMessage(): string
    {
        return "%s must be at least {$this->length} characters long";
    }
}
