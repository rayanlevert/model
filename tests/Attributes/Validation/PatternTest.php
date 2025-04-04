<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\Pattern;
use ReflectionProperty;

#[CoversClass(Pattern::class)]
class PatternTest extends TestCase
{
    #[Test]
    public function patternAttributeOnProperty(): void
    {
        $testClass = new class {
            #[Pattern('/^[a-zA-Z0-9_]+$/')]
            public string $username;
        };

        $attributes = new ReflectionProperty($testClass, 'username')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Pattern::class, $attributes[0]->newInstance());
        $this->assertSame('/^[a-zA-Z0-9_]+$/', $attributes[0]->newInstance()->pattern);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $pattern = new Pattern('/^[a-zA-Z0-9_]+$/');
        $this->assertSame($expected, $pattern->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $pattern = new Pattern('/^[a-zA-Z0-9_]+$/');
        $this->assertSame('%s must match the pattern /^[a-zA-Z0-9_]+$/', $pattern->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                            => [null, false],
            'non-string value'                      => [123, false],
            'empty string'                          => ['', false],
            'valid alphanumeric string'             => ['username123', true],
            'valid string with underscore'          => ['user_name', true],
            'string with spaces'                    => ['user name', false],
            'string with special characters'        => ['user@name', false],
            'string with uppercase and lowercase'   => ['UserName', true]
        ];
    }
} 