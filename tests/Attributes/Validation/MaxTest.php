<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\Max;
use ReflectionProperty;

#[CoversClass(Max::class)]
class MaxTest extends TestCase
{
    #[Test]
    public function maxAttributeOnProperty(): void
    {
        $testClass = new class {
            #[Max(100)]
            public float|int $price;
        };

        $attributes = new ReflectionProperty($testClass, 'price')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Max::class, $attributes[0]->newInstance());
        $this->assertSame(100, $attributes[0]->newInstance()->value);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $max = new Max(100);
        $this->assertSame($expected, $max->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $max = new Max(100);
        $this->assertSame('%s must be at most 100', $max->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                        => [null, false],
            'non-numeric string'                => ['abc', false],
            'value less than max'               => [50, true],
            'value equal to max'                => [100, true],
            'value greater than max'            => [150, false],
            'zero'                              => [0, true],
            'negative value'                    => [-50, true],
            'float less than max'               => [50.5, true],
            'float equal to max'                => [100.0, true],
            'float greater than max'            => [100.5, false],
            'float with many decimals'          => [99.99999, true],
            'float with scientific notation'    => [1e2, true],
            'float with negative exponent'      => [1e-2, true]
        ];
    }
} 