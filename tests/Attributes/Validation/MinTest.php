<?php

namespace RayanLevert\Model\Tests\Attributes\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation\Min;
use ReflectionProperty;

#[CoversClass(Min::class)]
class MinTest extends TestCase
{
    #[Test]
    public function minAttributeOnProperty(): void
    {
        $testClass = new class {
            #[Min(0)]
            public float|int $price;
        };

        $attributes = new ReflectionProperty($testClass, 'price')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Min::class, $attributes[0]->newInstance());
        $this->assertSame(0, $attributes[0]->newInstance()->value);
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function validate(mixed $value, bool $expected): void
    {
        $min = new Min(0);
        $this->assertSame($expected, $min->validate($value));
    }

    #[Test]
    public function getMessage(): void
    {
        $min = new Min(0);
        $this->assertSame('%s must be at least 0', $min->getMessage());
    }

    public static function validationProvider(): array
    {
        return [
            'null value'                        => [null, false],
            'non-numeric string'                => ['abc', false],
            'value less than min'               => [-50, false],
            'value equal to min'                => [0, true],
            'value greater than min'            => [50, true],
            'zero'                              => [0, true],
            'positive value'                    => [100, true],
            'float less than min'               => [-0.5, false],
            'float equal to min'                => [0.0, true],
            'float greater than min'            => [0.5, true],
            'float with many decimals'          => [0.00001, true],
            'float with scientific notation'    => [1e-1, true],
            'float with negative exponent'      => [-1e-1, false]
        ];
    }
} 