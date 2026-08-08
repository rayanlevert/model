<?php

namespace RayanLevert\Model\Tests\Columns;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Exception;

#[CoversClass(Type::class)]
class TypeTest extends \PHPUnit\Framework\TestCase
{
    /** Providers for getPhpValue tests (153-163) */
    public static function provideGetPhpValueIntTypes(): array
    {
        return [
            'INTEGER'          => [Type::INTEGER, 123, 123],
            'UNSIGNED_INTEGER' => [Type::UNSIGNED_INTEGER, 456, 456],
            'SMALL_INTEGER'    => [Type::SMALL_INTEGER, 1, 1],
            'TINY_INTEGER'     => [Type::TINY_INTEGER, '8', 8],
        ];
    }

    public static function provideGetPhpValueBoolTypes(): array
    {
        return [
            'BOOLEAN true'  => [Type::BOOLEAN, 1, true],
            'BOOLEAN false' => [Type::BOOLEAN, 0, false],
            'BOOLEAN bool true' => [Type::BOOLEAN, true, true],
            'BOOLEAN bool false' => [Type::BOOLEAN, false, false],
            'BOOLEAN string' => [Type::BOOLEAN, '0', false],
        ];
    }

    public static function provideGetPhpValueFloatTypes(): array
    {
        return [
            'DECIMAL' => [Type::DECIMAL, '3.14', 3.14],
            'FLOAT'   => [Type::FLOAT, 1.23, 1.23],
            'DOUBLE'  => [Type::DOUBLE, '2.718', 2.718],
        ];
    }

    public static function provideGetPhpValueStringTypes(): array
    {
        return [
            'CHAR'        => [Type::CHAR, 123, '123'],
            'VARCHAR'     => [Type::VARCHAR, true, '1'],
            'TEXT'        => [Type::TEXT, 5.67, '5.67'],
            'MEDIUM_TEXT' => [Type::MEDIUM_TEXT, null, ''],
            'LONG_TEXT'   => [Type::LONG_TEXT, 'my string', 'my string'],
        ];
    }

    public static function provideGetPhpValueDateTypes(): array
    {
        return [
            'DATE' => [Type::DATE, '2024-05-09', new DateTimeImmutable('2024-05-09 00:00:00')],
            'TIME' => [Type::TIME, '13:05:07', new DateTimeImmutable('1970-01-01 13:05:07')],
            'DATETIME' => [Type::DATETIME, '2023-12-08 15:16:17', new DateTimeImmutable('2023-12-08 15:16:17')],
            'TIMESTAMP 1715859045' => [Type::TIMESTAMP, '2024-05-16 11:30:45', new DateTimeImmutable('2024-05-16 11:30:45', new DateTimeZone('UTC'))],
        ];
    }

    public static function provideGetPhpValueBinaryTypes(): array
    {
        return [
            'BINARY'       => [Type::BINARY, '01010100', '01010100'],
            'VARBINARY'    => [Type::VARBINARY, 'varbinary', 'varbinary'],
            'BLOB'         => [Type::BLOB, "\x00\x01", "\x00\x01"],
            'MEDIUM_BLOB'  => [Type::MEDIUM_BLOB, 'medium', 'medium'],
            'LONG_BLOB'    => [Type::LONG_BLOB, 'longblob', 'longblob']
        ];
    }

    public static function provideGetPhpValueJsonTypes(): array
    {
        return [
            'JSON null'      => [Type::JSON, 'null', null],
            'JSON bool'      => [Type::JSON, 'true', true],
            'JSON array'     => [Type::JSON, '[1,2]', [1,2]],
            'JSON assoc'     => [Type::JSON, '{"a":1,"b":2}', (object) ['a' => 1, 'b' => 2]]
        ];
    }

    public static function provideNumericTypes(): array
    {
        return [
            'INTEGER'           => [Type::INTEGER],
            'UNSIGNED_INTEGER'  => [Type::UNSIGNED_INTEGER],
            'SMALL_INTEGER'     => [Type::SMALL_INTEGER],
            'TINY_INTEGER'      => [Type::TINY_INTEGER]
        ];
    }

    public static function provideStringTypes(): array
    {
        return [
            'CHAR'          => [Type::CHAR],
            'VARCHAR'       => [Type::VARCHAR],
            'TEXT'          => [Type::TEXT],
            'MEDIUM_TEXT'   => [Type::MEDIUM_TEXT],
            'LONG_TEXT'     => [Type::LONG_TEXT],
            'BINARY'        => [Type::BINARY],
            'VARBINARY'     => [Type::VARBINARY],
            'BLOB'          => [Type::BLOB],
            'MEDIUM_BLOB'   => [Type::MEDIUM_BLOB],
            'LONG_BLOB'     => [Type::LONG_BLOB]
        ];
    }

    public static function provideDateTimeTypes(): array
    {
        return [
            'DATE'      => [Type::DATE],
            'TIME'      => [Type::TIME],
            'DATETIME'  => [Type::DATETIME],
            'TIMESTAMP' => [Type::TIMESTAMP]
        ];
    }

    public static function provideScalarValues(): array
    {
        return [
            'integer' => [42],
            'float' => [3.14],
            'string' => ['test string'],
            'boolean true' => [true],
            'boolean false' => [false],
            'null' => [null]
        ];
    }

    public static function provideDateTimeValues(): array
    {
        $dateTime = new DateTime('2023-01-15 14:30:45');
        $dateTimeImmutable = new DateTimeImmutable('2023-01-15 14:30:45');

        return [
            'DATE with DateTime' => [Type::DATE, $dateTime, '2023-01-15'],
            'DATE with DateTimeImmutable' => [Type::DATE, $dateTimeImmutable, '2023-01-15'],
            'TIME with DateTime' => [Type::TIME, $dateTime, '14:30:45'],
            'TIME with DateTimeImmutable' => [Type::TIME, $dateTimeImmutable, '14:30:45'],
            'DATETIME with DateTime' => [Type::DATETIME, $dateTime, '2023-01-15 14:30:45'],
            'DATETIME with DateTimeImmutable' => [Type::DATETIME, $dateTimeImmutable, '2023-01-15 14:30:45'],
            'TIMESTAMP with DateTime' => [Type::TIMESTAMP, $dateTime, '2023-01-15 14:30:45'],
            'TIMESTAMP with DateTimeImmutable' => [Type::TIMESTAMP, $dateTimeImmutable, '2023-01-15 14:30:45']
        ];
    }

    #[Test]
    #[DataProvider('provideGetPhpValueIntTypes')]
    public function getPhpValueForIntTypes(Type $type, bool|float|int|string $in, int $expected): void
    {
        $this->assertSame($expected, $type->getPhpValue($in));
        $this->assertIsInt($type->getPhpValue($in));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueBoolTypes')]
    public function getPhpValueForBoolTypes(Type $type, bool|float|int|string $in, bool $expected): void
    {
        $this->assertSame($expected, $type->getPhpValue($in));
        $this->assertIsBool($type->getPhpValue($in));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueFloatTypes')]
    public function getPhpValueForFloatTypes(Type $type, bool|float|int|string $in, float $expected): void
    {
        $this->assertSame($expected, $type->getPhpValue($in));
        $this->assertIsFloat($type->getPhpValue($in));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueStringTypes')]
    public function getPhpValueForStringTypes(Type $type, null|bool|float|int|string $in, string $expected): void
    {
        $this->assertSame($expected, $type->getPhpValue($in ?? ''));
        $this->assertIsString($type->getPhpValue($in ?? ''));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueDateTypes')]
    public function getPhpValueForDateAndTimeTypes(Type $type, float|int|string $in, DateTimeImmutable $expected): void
    {
        $ret = $type->getPhpValue($in);
        $this->assertInstanceOf(DateTimeImmutable::class, $ret);
        $this->assertSame($expected->format('Y-m-d H:i:s'), $ret->format('Y-m-d H:i:s'));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueBinaryTypes')]
    public function getPhpValueForBinaryTypes(Type $type, float|int|string $in, string $expected): void
    {
        $this->assertSame($expected, $type->getPhpValue($in));
        $this->assertIsString($type->getPhpValue($in));
    }

    #[Test]
    #[DataProvider('provideGetPhpValueJsonTypes')]
    public function getPhpValueForJsonTypes(Type $type, float|int|string $in, mixed $expected): void
    {
        $this->assertEquals($expected, $type->getPhpValue($in));
    }

    #[Test]
    public function integerType(): void
    {
        $this->assertSame('INT', Type::INTEGER->value);
    }

    #[Test]
    public function unsignedIntegerType(): void
    {
        $this->assertSame('INT UNSIGNED', Type::UNSIGNED_INTEGER->value);
    }

    #[Test]
    public function booleanType(): void
    {
        $this->assertSame('TINYINT(1)', Type::BOOLEAN->value);
    }

    #[Test]
    public function varcharType(): void
    {
        $this->assertSame('VARCHAR', Type::VARCHAR->value);
    }

    #[Test]
    public function textType(): void
    {
        $this->assertSame('TEXT', Type::TEXT->value);
    }

    #[Test]
    public function timestampType(): void
    {
        $this->assertSame('TIMESTAMP', Type::TIMESTAMP->value);
    }

    #[Test]
    public function jsonType(): void
    {
        $this->assertSame('JSON', Type::JSON->value);
    }

    #[Test]
    #[DataProvider('provideScalarValues')]
    public function getValueReturnsScalarValuesAsIs(mixed $value): void
    {
        $type = Type::VARCHAR;
        $this->assertSame($value, $type->getQueryValue($value));
    }

    #[Test]
    #[DataProvider('provideDateTimeValues')]
    public function getValueFormatsDateTimeInterfaceCorrectly(Type $type, DateTimeInterface|int $dateTime, string $expected): void
    {
        $this->assertSame($expected, $type->getQueryValue($dateTime));
    }

    #[Test]
    public function getValueThrowsExceptionForDateTimeWithNonDateTimeType(): void
    {
        $dateTime = new DateTime('2023-01-01 12:00:00');
        $type = Type::VARCHAR;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('incorrect DateTimeInterface/Column type combination');

        $type->getQueryValue($dateTime);
    }

    #[Test]
    public function getValueUsesToStringMethodForObjects(): void
    {
        $object = new class {
            public function __toString(): string
            {
                return 'test string';
            }
        };

        $type = Type::VARCHAR;
        $this->assertSame('test string', $type->getQueryValue($object));
    }

    #[Test]
    public function getValueUsesSerializeMethodForObjects(): void
    {
        $object = new SerializableTestClass();

        $type = Type::VARCHAR;
        $serialized = $type->getQueryValue($object);
        $this->assertStringContainsString('serialized', $serialized);
    }

    #[Test]
    public function getValueUsesJsonEncodeForJsonSerializableObjects(): void
    {
        $object = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['key' => 'value'];
            }
        };

        $type = Type::VARCHAR;
        $result = $type->getQueryValue($object);
        $this->assertSame('{"key":"value"}', $result);
    }

    #[Test]
    public function getValueThrowsExceptionForNonScalarNonObjectValues(): void
    {
        $type = Type::VARCHAR;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cannot use a value of type array as a value for a column');

        $type->getQueryValue(['array' => 'value']);
    }

    #[Test]
    public function getValueThrowsExceptionForResourceTypes(): void
    {
        $type = Type::VARCHAR;
        $resource = fopen('php://memory', 'r');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cannot use a value of type resource as a value for a column');

        try {
            $type->getQueryValue($resource);
        } finally {
            fclose($resource);
        }
    }

    #[Test]
    public function getValueOkForCallableTypeAfterCalledWithCorrectType(): void
    {
        $type = Type::VARCHAR;
        $callable = function () {
            return 'test';
        };

        $this->assertSame('test', $type->getQueryValue($callable));
    }

    #[Test]
    public function getValueHandlesStringableInterfaceObjects(): void
    {
        $object = new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $type = Type::VARCHAR;
        $this->assertSame('stringable object', $type->getQueryValue($object));
    }

    #[Test]
    public function getValueHandlesLegacySerializableObjects(): void
    {
        $object = new LegacySerializableTestClass();

        $type = Type::VARCHAR;
        $serialized = $type->getQueryValue($object);
        $this->assertStringContainsString('legacy serialized', $serialized);
    }

    #[Test]
    public function getPhpValueNullReturnsNull(): void
    {
        $this->assertNull(Type::TIMESTAMP->getPhpValue(null));
    }
}

class SerializableTestClass
{
    public function __serialize(): array
    {
        return ['data' => 'serialized'];
    }
}

class LegacySerializableTestClass
{
    public function __serialize(): array
    {
        return ['data' => 'legacy serialized'];
    }
}
