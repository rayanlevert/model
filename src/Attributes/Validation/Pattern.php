<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

use function is_string;
use function preg_match;
use function sprintf;

/**
 * Pattern attribute for enforcing regex patterns
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     #[Pattern('/^[a-zA-Z0-9_]+$/')]
 *     private string $username;
 * }    
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Pattern extends Validation
{
    public function __construct(public readonly string $pattern) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    public function getMessage(): string
    {
        return sprintf('%s must match the pattern %s', '%s', $this->pattern);
    }
}
