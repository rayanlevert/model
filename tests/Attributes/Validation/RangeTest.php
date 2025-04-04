<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\Range;
use ReflectionProperty;

#[CoversClass(Range::class)]
class RangeTest extends TestCase
{
    #[Test]
    public function rangeAttributeOnProperty(): void
    {
        $testClass = new class {
            #[Range(0, 100)]
            public float|int $price;
        };

        $attributes = new ReflectionProperty($testClass, 'price')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Range::class, $attributes[0]->newInstance());
        $this->assertSame(0, $attributes[0]->newInstance()->min);
        $this->assertSame(100, $attributes[0]->newInstance()->max);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $range = new Range(0, 100);
        $this->assertSame($expected, $range->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $range = new Range(0, 100);
        $this->assertSame('%s must be between 0 and 100', $range->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                     => [null, false],
            'non-numeric string'             => ['abc', false],
            'value less than min'            => [-10, false],
            'value equal to min'             => [0, true],
            'value between min and max'      => [50, true],
            'value equal to max'             => [100, true],
            'value greater than max'         => [150, false],
            'zero'                           => [0, true],
            'negative value'                 => [-50, false],
            'float less than min'            => [-0.5, false],
            'float equal to min'             => [0.0, true],
            'float between min and max'      => [50.5, true],
            'float equal to max'             => [100.0, true],
            'float greater than max'         => [100.5, false],
            'float with many decimals'       => [99.99999, true],
            'float with scientific notation' => [1e1, true],
            'float with negative exponent'   => [1e-2, true]
        ];
    }
} 