<?php

namespace RayanLevert\Model\Tests\Fixtures;

use RayanLevert\Model\Attributes;
use RayanLevert\Model\Columns\Type;
use RayanLevert\Model\Model;

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
}
