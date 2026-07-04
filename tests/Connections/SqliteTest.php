<?php

namespace RayanLevert\Model\Tests\Connections;

use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Connections\Sqlite;

class SqliteTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function dsn(): void
    {
        $oConnection = new Sqlite('host');

        $this->assertSame('sqlite:host', $oConnection->dsn());
    }
}