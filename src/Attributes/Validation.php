<?php

namespace RayanLevert\Model\Attributes;

use Attribute;
use RayanLevert\Model\Exceptions\ValidationException;
use ReflectionAttribute;
use ReflectionClass;

use function sprintf;
use function is_subclass_of;
use function array_filter;
use function function_exists;

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
    public function getMessage(): string
    {
        return '%s is invalid';
    }

    /**
     * Validate all properties of an object having Validation attributes
     * 
     * @param object $object The object to validate
     *
     * @throws ValidationException If any validation fails
     */
    public static function validateProperties(object $object): void
    {
        foreach (new ReflectionClass($object)->getProperties() as $property) {
            // Get all attributes that extend Validation
            $attributes = array_filter(
                $property->getAttributes(),
                fn(ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), static::class)
            );

            // Add an error if the validation fails, every property is validated then returned in the error array
            foreach ($attributes as $attribute) {
                if (!$attribute->newInstance()->validate($property->getValue($object))) {
                    $errors[] = sprintf($attribute->newInstance()->getMessage(), $property->getName());
                }
            }
        }

        if (isset($errors)) {
            throw new ValidationException(...$errors);
        }
    }

    /** Returns the function to use for string operations, either with the extension `mbstring` or native `string` */
    final protected function stringFunction(string $function): callable
    {
        return function_exists("mb_$function") ? "mb_$function"(...) : $function(...);
    }
}
