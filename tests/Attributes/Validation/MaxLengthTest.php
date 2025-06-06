<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\MaxLength;
use ReflectionProperty;

#[CoversClass(MaxLength::class)]
class MaxLengthTest extends TestCase
{
    #[Test]
    public function maxLengthAttributeOnProperty(): void
    {
        $testClass = new class {
            #[MaxLength(50)]
            public string $username;
        };

        $attributes = new ReflectionProperty($testClass, 'username')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(MaxLength::class, $attributes[0]->newInstance());
        $this->assertSame(50, $attributes[0]->newInstance()->length);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, new MaxLength(5)->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $this->assertSame('%s must be at most 5 characters long', new MaxLength(5)->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                     => [null, false],
            'non-string value'               => [123, false],
            'empty string'                   => ['', true],
            'string shorter than max length' => ['abc', true],
            'string equal to max length'     => ['abcde', true],
            'string longer than max length'  => ['abcdef', false],
            'string with spaces'             => ['a b c', true],
            'string with special characters' => ['a@b#c', true]
        ];
    }
} 