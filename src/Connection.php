<?php

namespace RayanLevert\Model;

/**
 * Informations to connect to a database via the PDO `__construct`
 *
 * Abstract class allowing its children to easily handle parameters required by PHP for the PDO instance
 */
abstract class Connection implements \Stringable
{
    /** Prefix signifying the database type (mysql, pgsql, etc.) */
    public const string PREFIX = '';

    /** String used to mask sensitive information like passwords */
    public final const string MASKED_VALUE = '*****';

    /** Generates the DSN required to connect to the database from PHP's PDO */
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
        public readonly string $host,
        public readonly ?string $username = null,
        #[\SensitiveParameter] protected readonly ?string $password = null,
        public private(set) array $options = []
    ) {
        $this->options = \array_filter($this->options, fn(mixed $value) => \is_scalar($value));
    }

    /** Dumping this class doesn't display the password */
    public function __debugInfo(): array
    {
        return \array_merge(\get_object_vars($this), ['password' => self::MASKED_VALUE]);
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

    /** Returns a human-readable representation of the connection */
    public function __toString(): string
    {
        $parts = [
            "prefix: " . static::PREFIX,
            "host: {$this->host}",
            $this->username ? "username: {$this->username}" : null,
            $this->password ? "password: " . self::MASKED_VALUE : null,
            $this->options ? "options: " . json_encode($this->options) : null
        ];
        
        return implode(', ', array_filter($parts));
    }
}
