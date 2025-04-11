<?php

namespace RayanLevert\Model\Tests\Attributes;

use Attribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Exceptions\ValidationException;

#[CoversClass(Validation::class)]
class ValidationTest extends TestCase
{
    #[Test]
    public function getMessageReturnsDefaultMessage(): void
    {
        $object = new class extends Validation {
            public function validate(mixed $value): bool
            {
                return true;
            }
        };

        $this->assertSame('%s is invalid', $object->getMessage());
    }

    #[Test]
    public function validatePropertiesWithValidObject(): void
    {
        $object = new class {
            #[TestValidation(true)]
            public string $name = 'John Doe';

            #[TestValidation(true)]
            public int $age = 30;
        };

        // This should not throw an exception
        Validation::validateProperties($object);

        $this->assertTrue(true);
    }

    #[Test]
    public function validatePropertiesWithInvalidObject(): void
    {
        $object = new class {
            #[TestValidation(true)]
            public string $name = 'John Doe';

            #[TestValidation(false)]
            public int $age = 30;
        };

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('age is invalid');

        Validation::validateProperties($object);
    }

    #[Test]
    public function validatePropertiesWithMultipleInvalidProperties(): void
    {
        $object = new class {
            #[TestValidation(false)]
            public string $name = 'John Doe';

            #[TestValidation(false)]
            public int $age = 30;
        };

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('name is invalid, age is invalid');

        Validation::validateProperties($object);
    }

    #[Test]
    public function validatePropertiesWithNoValidationAttributes(): void
    {
        $object = new class {
            public string $name = 'John Doe';
            public int $age = 30;
        };

        // This should not throw an exception
        Validation::validateProperties($object);

        $this->assertTrue(true);
    }

    #[Test]
    public function validatePropertiesWithMixedValidationAttributes(): void
    {
        $object = new class {
            #[TestValidation(true)]
            public string $name = 'John Doe';

            public int $age = 30;

            #[TestValidation(false)]
            public string $email = 'invalid-email';
        };

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('email is invalid');

        Validation::validateProperties($object);
    }

    #[Test]
    public function validatePropertiesWithChildValidationClasses(): void
    {
        $object = new class {
            #[ChildValidation(true)]
            public string $name = 'John Doe';

            #[ChildValidation(false)]
            public int $age = 30;
        };

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('age is invalid');

        Validation::validateProperties($object);
    }
}

/**
 * Test validation attribute that always returns the specified result
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class TestValidation extends Validation
{
    private bool $result;

    public function __construct(bool $result)
    {
        $this->result = $result;
    }

    public function validate(mixed $value): bool
    {
        return $this->result;
    }
}

/**
 * Child validation class for testing inheritance
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ChildValidation extends Validation
{
    private bool $result;

    public function __construct(bool $result)
    {
        $this->result = $result;
    }

    public function validate(mixed $value): bool
    {
        return $this->result;
    }
}
