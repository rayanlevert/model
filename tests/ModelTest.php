<?php

namespace RayanLevert\Model\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\Column;
use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Exception;
use RayanLevert\Model\Exceptions\ValidationException;
use RayanLevert\Model\Model;
use RayanLevert\Model\State;
use stdClass;

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
    #[Test]
    public function table(): void
    {
        $model = new class extends Model {
            public string $table = 'users';
        };

        $this->assertSame('users', $model->table);
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

            public string $table = 'users';
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

                    public string $table = 'users';
                },
                ['name is required']
            ],
            'required property is empty string' => [
                new class extends Model {
                    #[Validation\Required]
                    public string $name = '';

                    public string $table = 'users';
                },
                ['name is required']
            ],
            'min value is less than minimum' => [
                new class extends Model {
                    #[Validation\Min(0)]
                    public float|int $price = -10;

                    public string $table = 'products';
                },
                ['price must be at least 0']
            ],
            'max value is greater than maximum' => [
                new class extends Model {
                    #[Validation\Max(100)]
                    public float|int $price = 150;

                    public string $table = 'products';
                },
                ['price must be at most 100']
            ],
            'multiple validation errors' => [
                new class extends Model {
                    #[Validation\Required]
                    public string $name = '';

                    #[Validation\Min(0)]
                    public float|int $price = -10;

                    public string $table = 'products';
                },
                [
                    'name is required',
                    'price must be at least 0'
                ]
            ]
        ];
    }

    #[Test]
    public function updateThrowsExceptionWhenModelIsTransiant(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot update an instance that is not persistent yet');

        $model->update();
    }

    #[Test]
    public function updateDoesNotThrowExceptionWhenModelIsPersistent(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        // This should not throw an exception
        $model->update();

        $this->assertTrue(true); // Assert that we reached this point
    }

    #[Test]
    public function updateDoesNotThrowExceptionWhenModelIsDetached(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        // Use reflection to set the state to DETACHED
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::DETACHED);

        // This should not throw an exception
        $model->update();

        $this->assertTrue(true); // Assert that we reached this point
    }

    #[Test]
    public function columnsOnePropertyNoName(): void
    {
        $model = new class extends Model {
            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';

            public string $table = 'test_table';
        };

        $this->assertSame(['name' => 'John Doe'], $model->columns());
    }

    #[Test]
    public function columnsOnePropertyWithName(): void
    {
        $model = new class extends Model {
            #[Column(Type::VARCHAR, 'first_name')]
            public string $firstName = 'John';

            public string $table = 'test_table';
        };

        $this->assertSame(['first_name' => 'John'], $model->columns());
    }

    #[Test]
    public function twoColumnsOneNoName(): void
    {
        $model = new class extends Model {
            #[Column(Type::VARCHAR)]
            public string $firstName = 'John';

            #[Column(Type::VARCHAR)]
            public string $lastName = 'Doe';

            public string $table = 'table_name';
        };

        $this->assertSame(['firstName' => 'John', 'lastName' => 'Doe'], $model->columns());
    }

    #[Test]
    public function columnsOnePropertyException(): void
    {
        $model = new class extends Model {
            #[Column(Type::VARCHAR)]
            public mixed $name;

            public string $table = 'test_table';

            public function onConstruct(): void
            {
                $this->name = fopen('php://memory', 'r');
            }
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($model::class . '::$name : cannot use a value of type resource as a value for a column');

        $model->columns();
    }

    #[Test]
    public function columnsNoColumns(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No columns found in ' . $model::class);

        $model->columns();
    }
}
