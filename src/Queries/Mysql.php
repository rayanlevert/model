<?php

namespace RayanLevert\Model\Queries;

/** Queries for MySQL databases */
class Mysql extends Rdbm
{
    public string $escapeCharacter = '`';
}
