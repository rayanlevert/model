<?php

namespace RayanLevert\Model\Tests\Connections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connections\Mysql;

#[CoversClass(Mysql::class)]
class MysqlTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function dsn(): void
    {
        $o = new Mysql('localhost');
        $this->assertSame('mysql:host=localhost', $o->dsn());

        $o = new Mysql('localhost', options: ['port' => 3306]);
        $this->assertSame('mysql:host=localhost;port=3306', $o->dsn());

        $o = new Mysql('localhost', options: ['port' => 3306, 'dbname' => 'test_database']);
        $this->assertSame('mysql:host=localhost;port=3306;dbname=test_database', $o->dsn());

        $o = new Mysql('', options: ['unix_socket' => '/tmp/mysql.sock', 'dbname' => 'test_database']);
        $this->assertSame('mysql:dbname=test_database;unix_socket=/tmp/mysql.sock', $o->dsn());

        $o = new Mysql('localhost', options: ['port' => 3306, 'dbname' => 'test_database', 'charset' => 'utf8mb4']);
        $this->assertSame('mysql:host=localhost;port=3306;dbname=test_database;charset=utf8mb4', $o->dsn());
    }
}
