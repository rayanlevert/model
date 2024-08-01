<?php

namespace RayanLevert\Model;

/**
 * Informations to connect to a database via the PDO `__construct`
 *
 * Abstract class allowing its children to easily handle parameters required by PHP for the PDO instance
 */
abstract class Connection
{
    /**
     * Generates the DSN required to connect to the database from PHP's PDO
     */
    abstract public function dsn(): string;

    /**
     * Informs the host of the connection, potential username, password and options to the PDO instance
     *
     * @param string $host Mandatory host to the database
     * @param string $username Optional username used to connect to the database
     * @param string $password Optional password used to connect to the database
     * @param array<string, string|int|float|bool> $options PDO options
     */
    final public function __construct(
        protected readonly string $host,
        protected readonly ?string $username = null,
        #[\SensitiveParameter] protected readonly ?string $password = null,
        protected array $options = []
    ) {
        $this->options = \array_filter($this->options, fn (mixed $value) => \is_scalar($value));
    }

    /**
     * Returns PDO ordered parameters to the constructor
     *
     * @return array{0: string, 1: ?string, 2: ?string, 3: array<string, string|int|float|bool>}
     */
    public function getPDOParameters(): array
    {
        return [$this->dsn(), $this->username, $this->password, $this->options];
    }

    /**
     * Sets an option to the PDO connection
     */
    public function setOption(string $name, string|int|float|bool $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Returns an option set from the constructor, or `::setOption()`
     *
     * @return string|int|float|bool Returns $default if the option has not been set
     */
    public function getOption(string $name, null|string|float|int|bool $default = null): null|string|float|int|bool
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Returns options
     *
     * @return array<string, string|int|float|bool>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** Returns the host from the constructor */
    public function getHost(): string
    {
        return $this->host;
    }

    /** Returns the username from the constructor */
    public function getUsername(): ?string
    {
        return $this->username;
    }


    /** Returns the password from the constructor */
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
