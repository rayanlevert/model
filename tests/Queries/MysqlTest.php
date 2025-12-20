<?php

namespace RayanLevert\Model\Tests\Queries;

use Exception;
use PHPUnit\Framework\Attributes\{CoversClass, Test};
use RayanLevert\Model\Queries\Mysql;
use RayanLevert\Model\Attributes\Column;
use RayanLevert\Model\Attributes\PrimaryKey;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Connections\Mysql as ConnectionsMysql;
use RayanLevert\Model\DataObject;
use RayanLevert\Model\Model;

#[CoversClass(Mysql::class)]
class MysqlTest extends \PHPUnit\Framework\TestCase
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
    public function createWithNoColumns(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';
        };

        $queries = new Mysql();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No columns found in ' . $model::class);

        $queries->create($model);
    }

    #[Test]
    public function createWithOneColumn(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';

            public string $table = 'users';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `users` (`name`) VALUES (?)", $result->query);
        $this->assertSame(['John Doe'], $result->values);
    }

    #[Test]
    public function createWithMultipleColumns(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $firstName = 'John';

            #[Column(Type::VARCHAR)]
            public string $lastName = 'Doe';

            #[Column(Type::INTEGER)]
            public int $age = 30;

            public string $table = 'users';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `users` (`firstName`, `lastName`, `age`) VALUES (?, ?, ?)", $result->query);
        $this->assertSame(['John', 'Doe', 30], $result->values);
    }

    #[Test]
    public function createWithCustomColumnNames(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR, 'first_name')]
            public string $firstName = 'John';

            #[Column(Type::VARCHAR, 'last_name')]
            public string $lastName = 'Doe';

            public string $table = 'users';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `users` (`first_name`, `last_name`) VALUES (?, ?)", $result->query);
        $this->assertSame(['John', 'Doe'], $result->values);
    }

    #[Test]
    public function createWithSpecialCharactersInValues(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $name = "O'Connor";

            #[Column(Type::TEXT)]
            public string $description = "It's a test with \"quotes\" and 'apostrophes'";

            public string $table = 'users';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `users` (`name`, `description`) VALUES (?, ?)", $result->query);
        $this->assertSame(["O'Connor", "It's a test with \"quotes\" and 'apostrophes'"], $result->values);
    }

    #[Test]
    public function createWithNumericValues(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::INTEGER)]
            public int $id = 1;

            #[Column(Type::FLOAT)]
            public float $price = 19.99;

            #[Column(Type::BOOLEAN)]
            public bool $active = true;

            public string $table = 'products';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `products` (`id`, `price`, `active`) VALUES (?, ?, ?)", $result->query);
        $this->assertSame([1, 19.99, true], $result->values);
    }

    #[Test]
    public function createWithNullValues(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public ?string $name = null;

            #[Column(Type::INTEGER)]
            public ?int $age = null;

            public string $table = 'users';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `users` (`name`, `age`) VALUES (?, ?)", $result->query);
        $this->assertSame([null, null], $result->values);
    }

    #[Test]
    public function createWithTableNameContainingSpecialCharacters(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $name = 'Test';

            public string $table = 'user_profiles';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `user_profiles` (`name`) VALUES (?)", $result->query);
        $this->assertSame(['Test'], $result->values);
    }

    #[Test]
    public function createWithMixedColumnTypes(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::INTEGER)]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'Test User';

            #[Column(Type::TEXT)]
            public string $description = 'A test description';

            #[Column(Type::BOOLEAN, 'is_active')]
            public bool $isActive = false;

            #[Column(Type::FLOAT)]
            public float $score = 95.5;

            public string $table = 'mixed_data';
        };

        $queries = new Mysql();
        $result  = $queries->create($model);

        $this->assertSame("INSERT INTO `mixed_data` (`id`, `name`, `description`, `is_active`, `score`) VALUES (?, ?, ?, ?, ?)", $result->query);
        $this->assertSame([1, 'Test User', 'A test description', false, 95.5], $result->values);
    }

    #[Test]
    public function updateWithNoColumns(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;
        };

        $queries = new Mysql();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No columns found in ' . $model::class);

        $queries->update($model);
    }

    #[Test]
    public function updateWithOneColumn(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $queries = new Mysql();
        $result  = $queries->update($model);

        $this->assertSame("UPDATE `users` SET `name` = ? WHERE `id` = ?", $result->query);
        $this->assertSame(['John Doe', 1], $result->values);
    }

    #[Test]
    public function updateWithMultipleColumns(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';

            #[Column(Type::INTEGER)]
            public int $age = 30;
        };

        $queries = new Mysql();
        $result  = $queries->update($model);

        $this->assertSame("UPDATE `users` SET `name` = ?, `age` = ? WHERE `id` = ?", $result->query);
        $this->assertSame(['John Doe', 30, 1], $result->values);
    }

    #[Test]
    public function deleteWithNoPrimaryKey(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model must have a primary key');

        $queries = new Mysql();
        $queries->delete($model);
    }

    #[Test]
    public function deleteWithPrimaryKey(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;
        };

        $queries = new Mysql();
        $result  = $queries->delete($model);

        $this->assertSame("DELETE FROM `users` WHERE `id` = ?", $result->query);
        $this->assertSame([1], $result->values);
    }

    #[Test]
    public function selectByPrimaryKeyWithNoPrimaryKey(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model must have a primary key');

        $queries = new Mysql();
        $queries->selectByPrimaryKey($model, 1);
    }

    #[Test]
    public function selectByPrimaryKeyWithPrimaryKey(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';

            #[PrimaryKey]
            #[Column(Type::INTEGER)]
            public int $id = 1;

            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';
        };

        $queries = new Mysql();
        $result  = $queries->selectByPrimaryKey($model, 1);

        $this->assertSame("SELECT * FROM `users` WHERE `id` = ?", $result->query);
        $this->assertSame([1], $result->values);
    }
}
