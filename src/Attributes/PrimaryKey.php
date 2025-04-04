<?php

namespace RayanLevert\Model\Attributes;

use Attribute;

/**
 * PrimaryKey attribute for marking a property a primary key
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     #[PrimaryKey]
 *     private int $id;
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey
{
    public function __construct() {}
}
