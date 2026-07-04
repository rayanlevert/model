<?php

namespace RayanLevert\Model\Connections;

/**
 * Connection to SQLite databases
 *
 * @see https://www.php.net/manual/en/ref.pdo-sqlite.connection.php
 *
 * Only the host has to be configured
 */
class Sqlite extends \RayanLevert\Model\Connection
{
    public const string PREFIX = 'sqlite';

    public function dsn(): string
    {
        return self::PREFIX . ':' . $this->host;
    }
}