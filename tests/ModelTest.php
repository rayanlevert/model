<?php

namespace RayanLevert\Model\Tests;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\AutoIncrement;
use RayanLevert\Model\Attributes\Column;
use RayanLevert\Model\Attributes\PrimaryKey;
use RayanLevert\Model\Attributes\Validation;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Exception;
use RayanLevert\Model\Exceptions\ValidationException;
use RayanLevert\Model\Model;
use RayanLevert\Model\State;
use RayanLevert\Model\Queries\Mysql;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Connections\Mysql as ConnectionsMysql;
use RayanLevert\Model\Queries\Statement;

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        Model::$dataObject = new DataObject(new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql());
    }

    protected function tearDown(): void
    {
        Model::$dataObject = null;
    }

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
        Model::$dataObject = new DataObject(new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql());

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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot update an instance that is not persistent or detached');

        $model->update();
    }

    #[Test]
    public function updateDoesNotThrowExceptionWhenModelIsPersistent(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'UPDATE `test_table` SET `name` = ? WHERE `id` = ?';
            }))
            ->willReturn(true);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        $model->update();
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot update an instance that is not persistent or detached');

        $model->update();
    }

    #[Test]
    public function updateValidatesThrowsException(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            #[Validation\Required]
            public ?string $name = null;
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('name is required');

        $model->update();
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

    #[Test]
    public function getPrimaryKeyNoPrimaryKey(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model must have a primary key');

        $model->getPrimaryKey();
    }

    #[Test]
    public function getPrimaryKeyNoColumn(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            public int $id = 1;
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model must have a primary key');

        $model->getPrimaryKey();
    }

    #[Test]
    public function getPrimaryKey(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;
        };

        $this->assertSame(1, $model->getPrimaryKey()->value);
        $this->assertSame('id', $model->getPrimaryKey()->column);
    }

    #[Test]
    public function getPrimaryKeyMultiplePrimaryKeys(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER, 'id_test')]
            public int $id = 1;

            #[PrimaryKey]
            #[Column(Type::VARCHAR)]
            public string $name = 'Test';
        };

        $this->assertSame(1, $model->getPrimaryKey()->value);
        $this->assertSame('id_test', $model->getPrimaryKey()->column);
    }

    #[Test]
    public function createThrowsExceptionWhenModelIsNotTransiant(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot create an instance that is not transiant');

        $model->create();
    }

    #[Test]
    public function createValidatesThrowsException(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            #[Validation\Required]
            public ?string $name = null;
        };

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('name is required');

        $model->create();
    }

    #[Test]
    public function createOk(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'INSERT INTO `test_table` (`name`) VALUES (?)';
            }))
            ->willReturn(true);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[AutoIncrement]
            #[Column(Type::INTEGER)]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $model->create();
    }

    #[Test]
    public function deleteThrowsExceptionWhenModelIsNotPersistent(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot delete an instance that is not persistent');

        $model->delete();
    }

    #[Test]
    public function deleteOk(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'DELETE FROM `test_table` WHERE `id` = ?';
            }))
            ->willReturn(true);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        $model->delete();
    }
}
