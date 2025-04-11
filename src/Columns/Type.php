<?php

namespace RayanLevert\Model\Columns;

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
} 