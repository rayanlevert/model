<?php

namespace RayanLevert\Model;

use PDO;
use RayanLevert\Model\Attributes;
use RayanLevert\Model\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionProperty;
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
     * Finds the first instance of the model by its primary key
     *
     * @param int|string $value The value of the primary key
     *
     * @throws Exception If the model doesn't have a primary key
     *
     * @return ?static The first instance of the model or null if no instance is found
     */
    public static function findFirstByPrimaryKey(int|string $value): ?static
    {
        $oModel     = new static();
        $oSelect    = static::$dataObject->queries->selectByPrimaryKey($oModel, $value);
        $oStatement = static::$dataObject->prepareAndExecute($oSelect);

        if (!$aResults = $oStatement->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        $oModel->assign($aResults);

        return $oModel;
    }

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
     * Creates the instance in the database (and assigns the AutoIncrement column value to the model)
     *
     * @throws Exception If the instance is not transiant or any error when generating the query
     * @throws ValidationException If any model validation fails
     */
    public function create(): void
    {
        if (State::TRANSIANT !== $this->state) {
            throw new Exception('Cannot create an instance that is not transiant');
        }

        $this->validate();

        static::$dataObject->prepareAndExecute(static::$dataObject->queries->create($this));

        if ($aiColumn = $this->getAutoIncrementColumn()) {
            $this->{$aiColumn} = static::$dataObject->pdo->lastInsertId();
        }

        $this->state = State::PERSISTENT;
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

        $this->state = State::DETACHED;
    }

    /**
     * Saves the instance to the database
     *
     * @throws Exception If the instance is not transiant or persistent or the query cannot be generated
     */
    public function save(): void
    {
        match ($this->state) {
            State::TRANSIANT  => $this->create(),
            State::PERSISTENT => $this->update(),
            State::DETACHED   => throw new Exception('Cannot save an instance that is not transiant or persistent')
        };
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
     * Returns the AutoIncrement column
     *
     * @throws Exception If no AutoIncrement column is found
     *
     * @return ?string The AutoIncrement column name
     */
    public function getAutoIncrementColumn(): ?string
    {
        return array_find(
            new ReflectionClass($this)->getProperties(),
            fn(ReflectionProperty $property) => $property->getAttributes(Attributes\AutoIncrement::class)
        )?->getName();
    }

    /**
     * Returns the columns and their values to be used in a query (excluding AutoIncrement columns)
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

    /**
     * Assigns the properties of the model from an array (property name or database column name)
     *
     * @throws Exception If a value doesn't have the same type of a property
     */
    public function assign(array $results): void
    {
        foreach ($results as $column => $value) {
            try {
                $propertyName = $this->getPropertyColumnName($column);
            } catch (Exception $e) {
                continue;
            }

            try {
                $this->{$propertyName} = $value;
            } catch (\TypeError $error) {
                throw new Exception("Setting property $column failed, " . $error->getMessage(), 0, $error);
            }
        }
    }

    /**
     * Returns the database column name for a given property name
     *
     * @param string $propertyName The property name
     * 
     * @throws Exception If the property is not found in the model
     *
     * @return string The database column name
     */
    public function getDatabaseColumnName(string $propertyName): string
    {
        try {
            if (!$oColumn = new ReflectionProperty($this, $propertyName)->getAttributes(Attributes\Column::class)[0] ?? null) {
                throw new Exception("Property $propertyName does not have a Column attribute in " . static::class);
            }
        } catch (\ReflectionException $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }

        return $oColumn->newInstance()->name ?: $propertyName;
    }

    /**
     * Returns the property name for a given database column name
     *
     * @param string $databaseColumnName The database column name
     *
     * @throws Exception If the property is not found in the model
     *
     * @return string The property name
     */
    public function getPropertyColumnName(string $columnName): string
    {
        foreach (new ReflectionClass($this)->getProperties() as $property) {
            if ($property->getName() === $columnName) {
                return $columnName;
            }

            if ($oColumn = $property->getAttributes(Attributes\Column::class)[0] ?? null) {
                if ($oColumn->newInstance()->name === $columnName) {
                    return $property->getName();
                }
            }
        }

        throw new Exception('Property for column ' . $columnName . ' not found in ' . static::class);
    }
}
