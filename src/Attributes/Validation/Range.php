<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

/**
 * Range attribute for enforcing numeric ranges
 *
 * Example:
 * ```php
 * class Product extends Model
 * {
 *     #[Range(0, 1000)]
 *     private float|int $price;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Range extends Validation
{
    public function __construct(
        public readonly float|int $min,
        public readonly float|int $max
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        return $value >= $this->min && $value <= $this->max;
    }

    public function getMessage(): string
    {
        return "%s must be between {$this->min} and {$this->max}";
    }
}
