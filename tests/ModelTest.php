<?php

namespace RayanLevert\Model\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Exceptions\ValidationException;
use RayanLevert\Model\Model;

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
    #[Test]
    public function table(): void
    {
        $model = new class extends Model {
            protected function table(): string
            {
                return 'users';
            }
        };

        $this->assertSame('users', $model->getTable());
    }

    #[Test]
    public function validateValidModel(): void
    {
        $model = new class extends Model {
            #[Validation\Required]
            public string $name = 'John Doe';

            #[Validation\Min(0)]
            #[Validation\Max(100)]
            public float|int $age = 30;

            protected function table(): string
            {
                return 'users';
            }
        };

        // This should not throw an exception
        $model->validate();

        $this->assertTrue(true);
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidModelsProvider')]
    public function validateInvalidModel(Model $model, array $expectedErrors): void
    {
        $this->expectException(ValidationException::class);

        try {
            $model->validate();
        } catch (ValidationException $e) {
            // Assert the errors array
            $this->assertSame($expectedErrors, $e->getErrors());

            // Assert the exception message
            $expectedMessage = implode(', ', $expectedErrors);
            $this->assertSame($expectedMessage, $e->getMessage());

            throw $e;
        }
    }

    public static function invalidModelsProvider(): array
    {
        return [
            'required property is null' => [
                new class extends Model {
                    #[Validation\Required]
                    public ?string $name = null;

                    protected function table(): string
                    {
                        return 'users';
                    }
                },
                ['name is required']
            ],
            'required property is empty string' => [
                new class extends Model {
                    #[Validation\Required]
                    public string $name = '';

                    protected function table(): string
                    {
                        return 'users';
                    }
                },
                ['name is required']
            ],
            'min value is less than minimum' => [
                new class extends Model {
                    #[Validation\Min(0)]
                    public float|int $price = -10;

                    protected function table(): string
                    {
                        return 'products';
                    }
                },
                ['price must be at least 0']
            ],
            'max value is greater than maximum' => [
                new class extends Model {
                    #[Validation\Max(100)]
                    public float|int $price = 150;

                    protected function table(): string
                    {
                        return 'products';
                    }
                },
                ['price must be at most 100']
            ],
            'multiple validation errors' => [
                new class extends Model {
                    #[Validation\Required]
                    public string $name = '';

                    #[Validation\Min(0)]
                    public float|int $price = -10;

                    protected function table(): string
                    {
                        return 'products';
                    }
                },
                [
                    'name is required',
                    'price must be at least 0'
                ]
            ]
        ];
    }
}
