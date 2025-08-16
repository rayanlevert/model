<?php

namespace RayanLevert\Model\Columns;

use DateTimeInterface;
use JsonSerializable;
use RayanLevert\Model\Exception;

use function is_scalar;
use function is_object;
use function method_exists;
use function serialize;
use function json_encode;
use function get_class;
use function gettype;
use function is_callable;
use function call_user_func;

/** Enum for common database column types used in PDO. This enum defines standard SQL data types and their corresponding PHP types. */
enum Type: string
{
    /** Integer type for primary keys and foreign keys. */
    case INTEGER = 'INT';

    /** Unsigned integer type for IDs and positive numbers. */
    case UNSIGNED_INTEGER = 'INT UNSIGNED';

    /** Small integer type for flags and status codes. */
    case SMALL_INTEGER = 'SMALLINT';

    /** Tiny integer type for boolean values. */
    case TINY_INTEGER = 'TINYINT';

    /** Boolean type (TINYINT(1)). */
    case BOOLEAN = 'TINYINT(1)';

    /** Decimal type for precise numbers. */
    case DECIMAL = 'DECIMAL';

    /** Float type for approximate numbers. */
    case FLOAT = 'FLOAT';

    /** Double precision floating point. */
    case DOUBLE = 'DOUBLE';

    /** Character type with fixed length. */
    case CHAR = 'CHAR';

    /** Variable-length character string. */
    case VARCHAR = 'VARCHAR';

    /** Text type for longer strings. */
    case TEXT = 'TEXT';

    /** Medium text type for very long strings. */
    case MEDIUM_TEXT = 'MEDIUMTEXT';

    /** Long text type for extremely long strings. */
    case LONG_TEXT = 'LONGTEXT';

    /** Date type for storing dates. */
    case DATE = 'DATE';

    /** Time type for storing times. */
    case TIME = 'TIME';

    /** Datetime type for storing date and time. */
    case DATETIME = 'DATETIME';

    /** Timestamp type for storing date and time with timezone. */
    case TIMESTAMP = 'TIMESTAMP';

    /** Binary type for storing binary data. */
    case BINARY = 'BINARY';

    /** VarBinary type for storing variable-length binary data. */
    case VARBINARY = 'VARBINARY';

    /** Blob type for storing large binary objects. */
    case BLOB = 'BLOB';

    /** MediumBlob type for storing medium-sized binary objects. */
    case MEDIUM_BLOB = 'MEDIUMBLOB';

    /** LongBlob type for storing large binary objects. */
    case LONG_BLOB = 'LONGBLOB';

    /** JSON type for storing JSON data. */
    case JSON = 'JSON';

    /** Get the PHP type corresponding to this SQL type. */
    public function getPhpType(): string
    {
        return match($this) {
            self::INTEGER, self::UNSIGNED_INTEGER,  self::SMALL_INTEGER, self::TINY_INTEGER => 'int',
            self::BOOLEAN => 'bool',
            self::DECIMAL, self::FLOAT, self::DOUBLE => 'float',
            self::CHAR, self::VARCHAR, self::TEXT, self::MEDIUM_TEXT, self::LONG_TEXT, => 'string',
            self::DATE, self::TIME, self::DATETIME, self::TIMESTAMP => 'DateTimeInterface',
            self::BINARY, self::VARBINARY, self::BLOB, self::MEDIUM_BLOB, 
            self::LONG_BLOB => 'string',
            self::JSON => 'mixed',
        };
    }

    /**
     * Returns the value to be used in a query
     *
     * @param mixed $value The value of the property
     *
     * @throws Exception If the value cannot be used in a query
     */
    public function getValue(mixed $value): mixed
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return match ($this) {
                self::DATE                      => $value->format('Y-m-d'),
                self::TIME                      => $value->format('H:i:s'),
                self::DATETIME, self::TIMESTAMP => $value->format('Y-m-d H:i:s'),
                default => throw new Exception('Incorrect DateTimeInterface/Column type combination'),
            };
        }

        if (is_callable($value)) {
            return $this->getValue(call_user_func($value));
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            } elseif (method_exists($value, '__serialize')) {
                return serialize($value);
            } elseif ($value instanceof JsonSerializable) {
                return json_encode($value);
            }

            throw new Exception('Cannot use an object of type ' . get_class($value) . ' as a value for a column');
        }

        throw new Exception('Cannot use a value of type ' . gettype($value) . ' as a value for a column');
    }
}
