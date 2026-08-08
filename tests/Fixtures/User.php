<?php

namespace RayanLevert\Model\Tests\Fixtures;

use DateTimeImmutable;
use RayanLevert\Model\Attributes;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Model;

/**
 * Additional datatypes you may want to test for thoroughness:
 *
 * - Type::BOOLEAN
 * - Type::DECIMAL
 * - Type::CHAR
 * - Type::TEXT, Type::MEDIUM_TEXT, Type::LONG_TEXT
 * - Type::BINARY, Type::VARBINARY, Type::BLOB, Type::MEDIUM_BLOB, Type::LONG_BLOB
 * - Type::JSON
 * - Type::UNSIGNED_INTEGER, Type::SMALL_INTEGER, Type::TINY_INTEGER
 * - Type::DATE
 */

/** User model for testing */
class User extends Model
{
    public string $table = 'user';

    #[Attributes\PrimaryKey]
    #[Attributes\Column(Type::INTEGER)]
    #[Attributes\AutoIncrement]
    public ?int $id = null;

    #[Attributes\Column(Type::VARCHAR, 'last_name')]
    #[Attributes\Validation\Required]
    public ?string $lastName = null;

    #[Attributes\Column(Type::VARCHAR, 'first_name')]
    #[Attributes\Validation\Required]
    public ?string $firstName = null;

    #[Attributes\Column(Type::DATETIME, 'created_at')]
    #[Attributes\Validation\Required]
    public ?DateTimeImmutable $createdAt = null;

    #[Attributes\Column(Type::INTEGER)]
    #[Attributes\Validation\Required]
    public ?int $age = null;

    #[Attributes\Column(Type::FLOAT)]
    #[Attributes\Validation\Required]
    public ?float $height = null;

    #[Attributes\Column(Type::TIME, 'wake_up_time')]
    public ?DateTimeImmutable $wakeUpTime = null;

    #[Attributes\Column(Type::TIMESTAMP, 'last_login')]
    public ?DateTimeImmutable $lastLoginTimestamp = null;

    #[Attributes\Column(Type::DATE, 'birthday')]
    public ?DateTimeImmutable $birthday = null;

    #[Attributes\Column(Type::JSON, 'preferences')]
    public ?object $preferences = null;

    #[Attributes\Column(Type::BLOB, 'avatar')]
    public ?string $avatar = null;
}
