<?php

namespace RayanLevert\Model;

/**
 * Informations to connect to a database via the PDO `__construct`
 *
 * Abstract class allowing its children to easily handle parameters required by PHP for the PDO instance
 */
abstract class Connection
{
    /** PDO Driver name */
    public const string DRIVER_NAME = '';

    /**
     * With information from the instance, generates the DNS required to connect to the database from PHP's PDO
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
     * @return string|int|float|bool Returns null if the option has not been set
     */
    public function getOption(string $name): null|string|float|int|bool
    {
        return $this->options[$name] ?? null;
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
}
