<?php

namespace RayanLevert\Model\Tests\Queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use RayanLevert\Model\Queries\Mysql;
use RayanLevert\Model\Attributes\Column;
use RayanLevert\Model\Columns\Type;
use PDO;

#[CoversClass(Mysql::class)]
class MysqlTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function createWithNoColumns(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            public string $table = 'users';
        };

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);

        $result = $queries->create($pdo);

        $this->assertSame('INSERT INTO `users` () VALUES ()', $result);
    }

    #[Test]
    public function createWithOneColumn(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $name = 'John Doe';

            public string $table = 'users';
        };

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('quote')
            ->with('John Doe')
            ->willReturn("'John Doe'");

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `users` (`name`) VALUES ('John Doe')", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->exactly(2))
            ->method('quote')
            ->willReturnMap([
                ['John', "'John'"],
                ['Doe', "'Doe'"]
            ]);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `users` (`firstName`, `lastName`, `age`) VALUES ('John', 'Doe', 30)", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->exactly(2))
            ->method('quote')
            ->willReturnMap([
                ['John', "'John'"],
                ['Doe', "'Doe'"]
            ]);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `users` (`first_name`, `last_name`) VALUES ('John', 'Doe')", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->exactly(2))
            ->method('quote')
            ->willReturnMap([
                ["O'Connor", "'O\\'Connor'"],
                ["It's a test with \"quotes\" and 'apostrophes'", "'It\\'s a test with \\\"quotes\\\" and \\'apostrophes\\''"]
            ]);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `users` (`name`, `description`) VALUES ('O\\'Connor', 'It\\'s a test with \\\"quotes\\\" and \\'apostrophes\\'')", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `products` (`id`, `price`, `active`) VALUES (1, 19.99, 1)", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `users` (`name`, `age`) VALUES (NULL, NULL)", $result);
    }

    #[Test]
    public function createWithTableNameContainingSpecialCharacters(): void
    {
        $model = new class extends \RayanLevert\Model\Model {
            #[Column(Type::VARCHAR)]
            public string $name = 'Test';

            public string $table = 'user_profiles';
        };

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('quote')
            ->with('Test')
            ->willReturn("'Test'");

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `user_profiles` (`name`) VALUES ('Test')", $result);
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

        $queries = new Mysql($model);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->exactly(2))
            ->method('quote')
            ->willReturnMap([
                ['Test User', "'Test User'"],
                ['A test description', "'A test description'"]
            ]);

        $result = $queries->create($pdo);

        $this->assertSame("INSERT INTO `mixed_data` (`id`, `name`, `description`, `is_active`, `score`) VALUES (1, 'Test User', 'A test description', 0, 95.5)", $result);
    }
}
