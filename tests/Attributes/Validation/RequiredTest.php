<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\Required;
use ReflectionProperty;

#[CoversClass(Required::class)]
class RequiredTest extends TestCase
{
    #[Test]
    public function requiredAttributeOnProperty(): void
    {
        $testClass = new class {
            #[Required]
            public string $name;
        };

        $attributes = new ReflectionProperty($testClass, 'name')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Required::class, $attributes[0]->newInstance());
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $required = new Required();
        $this->assertSame($expected, $required->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $required = new Required();
        $this->assertSame('%s is required', $required->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'        => [null, false],
            'empty string'      => ['', false],
            'non-empty string'  => ['test', true],
            'zero'              => [0, true],
            'false'             => [false, true],
            'empty array'       => [[], true]
        ];
    }
} 