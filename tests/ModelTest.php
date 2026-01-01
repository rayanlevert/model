<?php

namespace RayanLevert\Model\Tests;

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
            ->willReturn(new PDOStatement);

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
    public function getAutoIncrementColumnNoAutoIncrement(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->assertNull($model->getAutoIncrementColumn());
    }

    #[Test]
    public function getAutoIncrementColumn(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[AutoIncrement]
            #[Column(Type::INTEGER)]
            public ?int $id = null;
        };

        $this->assertSame('id', $model->getAutoIncrementColumn());
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
    public function createWithAutoIncrementOk(): void
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
            ->willReturn(new PDOStatement);

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

        // Mocked DataObject returns 0 for lastInsertId
        $this->assertSame(0, $model->id);

        $this->assertSame(State::PERSISTENT, $model->state);
    }

    #[Test]
    public function createWithoutAutoIncrementOk(): void
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
            ->willReturn(new PDOStatement);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $model->create();

        $this->assertSame(State::PERSISTENT, $model->state);
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
            ->willReturn(new PDOStatement);

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

        $this->assertSame(State::DETACHED, $model->state);
    }

    #[Test]
    public function saveThrowsExceptionWhenModelIsNotTransiantOrPersistent(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        // Use reflection to set the state to DETACHED
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::DETACHED);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot save an instance that is not transiant or persistent');

        $model->save();
    }

    #[Test]
    public function saveTransiant(): void
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
            ->willReturn(new PDOStatement);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            #[AutoIncrement]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $model->save();

        $this->assertSame(State::PERSISTENT, $model->state);
    }

    #[Test]
    public function savePersistent(): void
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
            ->willReturn(new PDOStatement);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            #[AutoIncrement]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        // Use reflection to set the state to PERSISTENT
        $reflection = new \ReflectionProperty($model, 'state');
        $reflection->setValue($model, State::PERSISTENT);

        $model->save();

        $this->assertSame(State::PERSISTENT, $model->state);
    }

    #[Test]
    public function assignThrowsException(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Setting property name failed, Cannot assign array to property');

        $model->assign(['name' => []]);
    }

    #[Test]
    public function assignOk(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR)]
            public ?string $name = null;

            #[Column(Type::INTEGER)]
            public ?int $age = null;
        };

        $model->assign(['name' => 'John Doe', 'age' => 30]);

        $this->assertSame('John Doe', $model->name);
        $this->assertSame(30, $model->age);
    }

    #[Test]
    public function assignOkColumnNameDifferentThanProperty(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR, 'first_name')]
            public ?string $name = null;

            #[Column(Type::INTEGER)]
            public ?int $age = null;
        };

        $model->assign(['first_name' => 'John Doe', 'age' => 30]);

        $this->assertSame('John Doe', $model->name);
        $this->assertSame(30, $model->age);
    }

    #[Test]
    public function findFirstByPrimaryKey(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oPDOStatementMock = $this->getMockBuilder(PDOStatement::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $oPDOStatementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'name' => 'John Doe', 'property_not_in_model' => 'value']);

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'SELECT * FROM `test_table` WHERE `id` = ?';
            }))
            ->willReturn($oPDOStatementMock);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            #[AutoIncrement]
            public ?int $id = null;

            #[Column(Type::VARCHAR)]
            public string $name = '';
        };

        $result = $model->findFirstByPrimaryKey(1);

        $this->assertSame(1, $result->id);
        $this->assertSame('John Doe', $result->name);
    }

    #[Test]
    public function findFirstByPrimaryKeyColumnNotSameTypeOfProperty(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oPDOStatementMock = $this->getMockBuilder(PDOStatement::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $oPDOStatementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'name' => []]);

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'SELECT * FROM `test_table` WHERE `id` = ?';
            }))
            ->willReturn($oPDOStatementMock);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            #[AutoIncrement]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        try {
            $model->findFirstByPrimaryKey(1);
        } catch (Exception $e) {
            $this->assertStringStartsWith('Setting property name failed, Cannot assign array to property', $e->getMessage());

            return;
        }

        $this->fail('Expected exception not thrown');
    }

    #[Test]
    public function getDatabaseColumnNamePropertyNotFound(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(Exception::class);

        $model->getDatabaseColumnName('test');
    }

    #[Test]
    public function getDatabaseColumnNamePropertyFoundNoColumn(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            public string $name = 'John Doe';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Property name does not have a Column attribute in " . $model::class);

        $model->getDatabaseColumnName('name');
    }

    #[Test]
    public function getDatabaseColumnNameOk(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $this->assertSame('name', $model->getDatabaseColumnName('name'));
    }

    #[Test]
    public function getDatabaseColumnNameWithColumnName(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR, 'first_name')]
            public string $name = 'John Doe';
        };

        $this->assertSame('first_name', $model->getDatabaseColumnName('name'));
    }

    #[Test]
    public function getPropertyColumnNamePropertyNotFound(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Property for column test not found in " . $model::class);

        $model->getPropertyColumnName('test');
    }

    #[Test]
    public function getPropertyColumnNameOk(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $this->assertSame('name', $model->getPropertyColumnName('name'));
    }

    #[Test]
    public function getPropertyColumnNameWithColumnName(): void
    {
        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR, 'first_name')]
            public string $name = 'John Doe';
        };

        $this->assertSame('name', $model->getPropertyColumnName('first_name'));
    }

    #[Test]
    public function findFirstByColumnsOne(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oPDOStatementMock = $this->getMockBuilder(PDOStatement::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $oPDOStatementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['first_name' => 'John', 'property_not_in_model' => 'value']);

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'SELECT * FROM `test_table` WHERE `first_name` = ?';
            }))
            ->willReturn($oPDOStatementMock);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[Column(Type::VARCHAR, 'first_name')]
            public string $name = '';
        };

        $result = $model->findFirstByColumns(['name' => 'John']);

        $this->assertSame('John', $result->name);
    }

    #[Test]
    public function findFirstByColumnsMultiple(): void
    {
        $oDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->setConstructorArgs([new ConnectionsMysql('percona', 'root', 'root-password'), new Mysql()])
            ->onlyMethods(['prepareAndExecute'])
            ->getMock();

        $oPDOStatementMock = $this->getMockBuilder(PDOStatement::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $oPDOStatementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['first_name' => 'John', 'age' => 30, 'id' => 10]);

        $oDataObjectMock->expects($this->once())
            ->method('prepareAndExecute')
            ->with($this->callback(function (Statement $statement) {
                return $statement->query === 'SELECT * FROM `test_table` WHERE `first_name` = ? AND `age` = ?';
            }))
            ->willReturn($oPDOStatementMock);

        Model::$dataObject = $oDataObjectMock;

        $model = new class extends Model {
            public string $table = 'test_table';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            #[AutoIncrement]
            public ?int $id = null;

            #[Column(Type::VARCHAR, 'first_name')]
            public string $name = '';

            #[Column(Type::INTEGER)]
            public int $age = 0;
        };

        $result = $model->findFirstByColumns(['name' => 'John', 'age' => 30]);

        $this->assertSame('John', $result->name);
        $this->assertSame(30, $result->age);
        $this->assertSame(10, $result->id);
    }
}
