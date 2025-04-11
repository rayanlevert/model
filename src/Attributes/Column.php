<?php

namespace RayanLevert\Model\Attributes;

use Attribute;
use RayanLevert\Model\Columns\Type;

/** Column attribute defining how is described the column in the database */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    /** Value when a parameter is not passed to differentiate NULL */
    public final const string UNDEFINED = 'undefined';

    /**
     * @param Type $type Type of the column in the database
     * @param ?string $name Name of the column, if NULL the property name will be taken in account
     * @param mixed $defaultValue Default value
     * @param boolean $nullable If the column is nullable, not by default
     * @param ?int $length The length of the data type
     */
    public function __construct(
        public readonly Type $type,
        public readonly ?string $name = null,
        public readonly mixed $defaultValue = self::UNDEFINED,
        public readonly bool $nullable = false,
        public readonly ?int $length = null
    ) {}
}
