<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

use function is_numeric;
use function sprintf;

/**
 * Min attribute for enforcing minimum numeric values
 *
 * Example:
 * ```php
 * class Product extends Model
 * {
 *     #[Min(0)]
 *     private float|int $price;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends Validation
{
    public function __construct(public readonly float|int $value) {}

    public function validate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        return $value >= $this->value;
    }

    public function getMessage(): string
    {
        return sprintf('%s must be at least %g', '%s', $this->value);
    }
}
