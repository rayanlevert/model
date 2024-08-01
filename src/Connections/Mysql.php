<?php

namespace RayanLevert\Model\Connections;

/**
 * Connection to MySQL databases
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
    public function dsn(): string
    {
        $options = [
            'host'        => $this->host,
            'port'        => $this->getOption('port'),
            'dbname'      => $this->getOption('dbname'),
            'unix_socket' => $this->getOption('unix_socket'),
            'charset'     => $this->getOption('charset')
        ];

        $dsn = '';
        foreach (\array_filter($options) as $name => $value) {
            $dsn .= "$name=$value;";
        }

        return \rtrim("mysql:$dsn", ';');
    }
}
