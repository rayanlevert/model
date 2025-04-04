<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\MinLength;
use ReflectionProperty;

#[CoversClass(MinLength::class)]
class MinLengthTest extends TestCase
{
    #[Test]
    public function minLengthAttributeOnProperty(): void
    {
        $testClass = new class {
            #[MinLength(3)]
            public string $username;
        };

        $attributes = new ReflectionProperty($testClass, 'username')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(MinLength::class, $attributes[0]->newInstance());
        $this->assertSame(3, $attributes[0]->newInstance()->length);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $minLength = new MinLength(3);
        $this->assertSame($expected, $minLength->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $minLength = new MinLength(3);
        $this->assertSame('%s must be at least 3 characters long', $minLength->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                     => [null, false],
            'non-string value'               => [123, false],
            'empty string'                   => ['', false],
            'string shorter than min length' => ['ab', false],
            'string equal to min length'     => ['abc', true],
            'string longer than min length'  => ['abcd', true],
            'string with spaces'             => ['a b', true],
            'string with special characters' => ['a@b', true]
        ];
    }
} 