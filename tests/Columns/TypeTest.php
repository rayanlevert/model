<?php

namespace RayanLevert\Model\Tests\Columns;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Columns\Type;

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
} 