<?php

namespace RayanLevert\Model;

use RayanLevert\Model\Attributes;
use ReflectionClass;
use stdClass;


abstract class Model
{
    /** DataObject instance to communicate with the database */
    public static ?DataObject $dataObject = null;

    /** State of the instance */
    public protected(set) State $state = State::TRANSIANT;

    /** @var string The database table name */
    abstract public string $table { get; }

    /**
     * @throws Exception If the queries class is not set (to be able to generate queries according to the database used)
     */
    final public function __construct()
    {
        if (!static::$dataObject) {
            throw new Exception('DataObject class not set, it must be set via the static property $dataObject');
        }

        $this->onConstruct();
    }

    /** Called at the end of the constructor */
    public function onConstruct(): void {}

    /**
     * Creates the instance in the database
     *
     * @throws Exception If the instance is not transiant
     */
    public function create(): void
    {
        if (State::TRANSIANT !== $this->state) {
            throw new Exception('Cannot create an instance that is not transiant');
        }
        
        $this->validate();

        static::$dataObject->prepareAndExecute(static::$dataObject->queries->create($this));
    }

    /**
     * Updates the instance to the database
     *
     * @throws Exception If the instance is not persistent or detached
     */
    public function update(): void
    {
        if (State::PERSISTENT !== $this->state) {
            throw new Exception('Cannot update an instance that is not persistent or detached');
        }

        $this->validate();

        static::$dataObject->prepareAndExecute(static::$dataObject->queries->update($this));
    }

    /**
     * Deletes the instance from the database
     *
     * @throws Exception If the instance is not detached
     */
    public function delete(): void
    {
        if (State::PERSISTENT !== $this->state) {
            throw new Exception('Cannot delete an instance that is not persistent');
        }

        static::$dataObject->prepareAndExecute(static::$dataObject->queries->delete($this));
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
    final public function getPrimaryKey(): stdClass
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
     * Returns the columns and their values to be used in a query (no AutoIncrement)
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
            } elseif ($property->getAttributes(Attributes\AutoIncrement::class)) {
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
