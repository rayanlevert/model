<?php

namespace RayanLevert\Model\Attributes;

use Attribute;

/** Abstract base class for all validation attributes */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Validation
{
    /**
     * Validate the given value
     * 
     * @param mixed $value The value to validate
     * @return bool Whether the value is valid
     */
    abstract public function validate(mixed $value): bool;
    
    /**
     * Returns the validation error message
     *
     * `sprintf()` is used to format the message with the property name (%s)
     */
    abstract public function getMessage(): string;
} 