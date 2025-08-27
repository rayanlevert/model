<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

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
     * Returns the first property with PrimaryKey attribute
     *
     * @throws Exception If no primary key property is found
     *
     * @return object{column: string, value: mixed} The primary key with the column name and its value
     */
    final public function getPrimaryKeyProperty(): stdClass
    {
        foreach (new ReflectionClass($this)->getProperties() as $property) {
            $oColumn = $property->getAttributes(Attributes\Column::class)[0] ?? null;

            if ($property->getAttributes(Attributes\PrimaryKey::class) && $oColumn) {
                return (object) [
                    'column' => $oColumn->newInstance()->name ?: $property->getName(),
                    'value'  => $property->getValue($this)
                ];
            }
        }
        
        throw new Exception('Model must have a primary key');
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

        return $columns ?? throw new Exception('No columns found in ' . static::class);
    }
}
