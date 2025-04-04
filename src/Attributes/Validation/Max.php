<?php

namespace RayanLevert\Model\Attributes\Validation;

use Attribute;
use RayanLevert\Model\Attributes\Validation;

/**
 * Max attribute for enforcing maximum numeric values
 *
 * Example:
 * ```php
 * class Product extends Model
 * {
 *     #[Max(1000)]
 *     private float|int $price;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends Validation
{
    public function __construct(public readonly float|int $value) {}
    
    public function validate(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        return $value <= $this->value;
    }
    
    public function getMessage(): string
    {
        return sprintf('%s must be at most %g', $this->propertyName ?? '%s', $this->value);
    }
} 