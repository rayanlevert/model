<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

/**
 * MaxLength attribute for enforcing maximum string length
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     #[MaxLength(50)]
 *     private string $username;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MaxLength extends Validation
{
    public function __construct(public readonly int $length) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return mb_strlen($value) <= $this->length;
    }

    public function getMessage(): string
    {
        return sprintf('%s must be at most %d characters long', $this->propertyName ?? '%s', $this->length);
    }
}
