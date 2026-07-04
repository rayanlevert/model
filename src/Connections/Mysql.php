<?php

namespace RayanLevert\Model\Connections;

/**
 * Connection to MySQL databases
 *
 * @see https://www.php.net/manual/en/ref.pdo-mysql.connection.php
 *
 * Additional elements for the DSN to set as options:
 *
 * - host: The hostname on which the database server resides
 * - port: The port number where the database server is listening
 * - dbname: The name of the database
 * - unix_socket: The MySQL Unix socket (shouldn't be used with host or port).
 * - charset: The character set
 */
class Mysql extends \RayanLevert\Model\Connection
{
    public const string PREFIX = 'mysql';

    public function dsn(): string
    {
        return $this->concatDSN([
            'host'        => $this->host,
            'port'        => $this->getOption('port'),
            'dbname'      => $this->getOption('dbname'),
            'unix_socket' => $this->getOption('unix_socket'),
            'charset'     => $this->getOption('charset')
        ]);
    }
}
