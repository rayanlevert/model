<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

/**
 * Required attribute for marking a property as required
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     #[Required]
 *     private string $name;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Required extends Validation
{
    public function __construct() {}

    public function validate(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    public function getMessage(): string
    {
        return '%s is required';
    }
}
