<?php

namespace RayanLevert\Model\Connections;

use RayanLevert\Model\Connection;

/**
 * Connection to PostgreSQL databases
 *
 * @see https://www.php.net/manual/en/ref.pdo-pgsql.connection.php
 *
 * Additional elements for the DSN to set as options:
 *
 * - host: The hostname on which the database server resides
 * - port: The port number where the database server is listening
 * - dbname: The name of the database
 * - user: The username to connect to the database
 * - password: The password to connect to the database
 * - sslmode: The SSL mode to use for the connection
 */
class Postgres extends Connection
{
    public const string PREFIX = 'pgsql';

    public function dsn(): string
    {
        return $this->concatDSN([
            'host'     => $this->host,
            'port'     => $this->getOption('port'),
            'dbname'   => $this->getOption('dbname'),
            'user'     => $this->username,
            'password' => $this->password,
            'sslmode'  => $this->getOption('sslmode')
        ]);
    }
}