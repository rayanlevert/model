<?php

namespace RayanLevert\Model\Tests\Columns;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Stringable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Exception;
use JsonSerializable;

#[CoversClass(Type::class)]
class TypeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function integerType(): void
    {
        $this->assertSame('INT', Type::INTEGER->value);
        $this->assertSame('int', Type::INTEGER->getPhpType());
    }

    #[Test]
    public function unsignedIntegerType(): void
    {
        $this->assertSame('INT UNSIGNED', Type::UNSIGNED_INTEGER->value);
        $this->assertSame('int', Type::UNSIGNED_INTEGER->getPhpType());
    }

    #[Test]
    public function booleanType(): void
    {
        $this->assertSame('TINYINT(1)', Type::BOOLEAN->value);
        $this->assertSame('bool', Type::BOOLEAN->getPhpType());
    }

    #[Test]
    public function varcharType(): void
    {
        $this->assertSame('VARCHAR', Type::VARCHAR->value);
        $this->assertSame('string', Type::VARCHAR->getPhpType());
    }

    #[Test]
    public function textType(): void
    {
        $this->assertSame('TEXT', Type::TEXT->value);
        $this->assertSame('string', Type::TEXT->getPhpType());
    }

    #[Test]
    public function timestampType(): void
    {
        $this->assertSame('TIMESTAMP', Type::TIMESTAMP->value);
        $this->assertSame('DateTimeInterface', Type::TIMESTAMP->getPhpType());
    }

    #[Test]
    public function jsonType(): void
    {
        $this->assertSame('JSON', Type::JSON->value);
        $this->assertSame('mixed', Type::JSON->getPhpType());
    }

    #[Test]
    #[DataProvider('provideNumericTypes')]
    public function numericTypesReturnIntPhpType(Type $type): void
    {
        $this->assertSame('int', $type->getPhpType());
    }

    #[Test]
    #[DataProvider('provideStringTypes')]
    public function stringTypesReturnStringPhpType(Type $type): void
    {
        $this->assertSame('string', $type->getPhpType());
    }

    #[Test]
    #[DataProvider('provideDateTimeTypes')]
    public function dateTimeTypesReturnDateTimeInterfacePhpType(Type $type): void
    {
        $this->assertSame('DateTimeInterface', $type->getPhpType());
    }

    // getValue method tests

    #[Test]
    #[DataProvider('provideScalarValues')]
    public function getValueReturnsScalarValuesAsIs(mixed $value): void
    {
        $type = Type::VARCHAR;
        $this->assertSame($value, $type->getValue($value));
    }

    #[Test]
    #[DataProvider('provideDateTimeValues')]
    public function getValueFormatsDateTimeInterfaceCorrectly(Type $type, DateTimeInterface $dateTime, string $expected): void
    {
        $this->assertSame($expected, $type->getValue($dateTime));
    }

    #[Test]
    public function getValueThrowsExceptionForDateTimeWithNonDateTimeType(): void
    {
        $dateTime = new DateTime('2023-01-01 12:00:00');
        $type = Type::VARCHAR;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Incorrect DateTimeInterface/Column type combination');

        $type->getValue($dateTime);
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
        $this->assertSame('test string', $type->getValue($object));
    }

    #[Test]
    public function getValueUsesSerializeMethodForObjects(): void
    {
        $object = new SerializableTestClass();

        $type = Type::VARCHAR;
        $serialized = $type->getValue($object);
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
        $result = $type->getValue($object);
        $this->assertSame('{"key":"value"}', $result);
    }

    #[Test]
    public function getValueThrowsExceptionForObjectsWithoutRequiredMethods(): void
    {
        $object = new \stdClass();
        $type = Type::VARCHAR;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use an object of type stdClass as a value for a column');

        $type->getValue($object);
    }

    #[Test]
    public function getValueThrowsExceptionForNonScalarNonObjectValues(): void
    {
        $type = Type::VARCHAR;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use a value of type array as a value for a column');

        $type->getValue(['array' => 'value']);
    }

    #[Test]
    public function getValueThrowsExceptionForResourceTypes(): void
    {
        $type = Type::VARCHAR;
        $resource = fopen('php://memory', 'r');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use a value of type resource as a value for a column');

        try {
            $type->getValue($resource);
        } finally {
            fclose($resource);
        }
    }

    #[Test]
    public function getValueThrowsExceptionForCallableTypeAfterCalledNotCorrectType(): void
    {
        $type = Type::VARCHAR;
        $callable = function() { return new \stdClass(); };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot use an object of type stdClass as a value for a column');

        $type->getValue($callable);
    }

    #[Test]
    public function getValueOkForCallableTypeAfterCalledWithCorrectType(): void
    {
        $type = Type::VARCHAR;
        $callable = function() { return 'test'; };

        $this->assertSame('test', $type->getValue($callable));
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
        $this->assertSame('stringable object', $type->getValue($object));
    }

    #[Test]
    public function getValueHandlesLegacySerializableObjects(): void
    {
        $object = new LegacySerializableTestClass();

        $type = Type::VARCHAR;
        $serialized = $type->getValue($object);
        $this->assertStringContainsString('legacy serialized', $serialized);
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