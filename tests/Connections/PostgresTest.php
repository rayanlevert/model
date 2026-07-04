<?php

namespace RayanLevert\Model\Tests\Connections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connections\Postgres;

#[CoversClass(Postgres::class)]
class PostgresTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function dsn(): void
    {
        $o = new Postgres('localhost');
        $this->assertSame('pgsql:host=localhost', $o->dsn());

        $o = new Postgres('localhost', options: ['port' => 5432]);
        $this->assertSame('pgsql:host=localhost;port=5432', $o->dsn());

        $o = new Postgres('localhost', options: ['port' => 5432, 'dbname' => 'test_database']);
        $this->assertSame('pgsql:host=localhost;port=5432;dbname=test_database', $o->dsn());

        $o = new Postgres('localhost', username: 'test_user', password: 'test_password', options: ['port' => 5432, 'dbname' => 'test_database']);
        $this->assertSame('pgsql:host=localhost;port=5432;dbname=test_database;user=test_user;password=test_password', $o->dsn());

        $o = new Postgres('localhost', username: 'test_user', password: 'test_password', options: ['port' => 5432, 'dbname' => 'test_database', 'sslmode' => 'require']);
        $this->assertSame('pgsql:host=localhost;port=5432;dbname=test_database;user=test_user;password=test_password;sslmode=require', $o->dsn(), $o->dsn());
    }
}
