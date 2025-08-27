<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes;
use ReflectionClass;

abstract class Model
{
    public protected(set) State $state = State::TRANSIANT;

    /** @var string The database table name */
    abstract public string $table { get; }

    final public function __construct()
    {
        $this->onConstruct();
    }

    /** Called at the end of the constructor */
    public function onConstruct(): void {}

    /**
     * Updates the instance to the database
     *
     * @throws Exception If the instance is not persistent yet
     */
    public function update(): void
    {
        if (State::TRANSIANT === $this->state) {
            throw new Exception('Cannot update an instance that is not persistent yet');
        }
    }

    /**
     * Validate all properties with Validation attributes
     * 
     * @throws ValidationException If any validation fails
     */
    public function validate(): void
    {
        Attributes\Validation::validateProperties($this);
    }

    /**
     * Returns the columns and their values to be used in a query
     *
     * @throws Exception If a property's value cannot be used in a query or no column is found
     *
     * @return array<string, mixed> The columns (database column name => PHP value)
     */
    public function columns(): array
    {
        foreach (new ReflectionClass($this)->getProperties() as $property) {
            if (!$attributes = $property->getAttributes(Attributes\Column::class)) {
                continue;
            }

            $oColumn = $attributes[0]->newInstance();

            try {
                $phpValue = $oColumn->type->getValue($property->getValue($this));
            } catch (Exception $e) {
                throw new Exception(static::class . '::$' . $property->getName() . ' : ' . $e->getMessage());
            }

            $columns[$oColumn->name ?: $property->getName()] = $phpValue;
        }

        if (empty($columns)) {
            throw new Exception('No columns found in ' . static::class);
        }

        return $columns ?? [];
    }
}
