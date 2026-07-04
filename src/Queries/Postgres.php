<?php

namespace RayanLevert\Model\Queries;

/** Queries for PostgreSQL databases */
class Postgres extends Rdbm
{
    public string $escapeCharacter = '"';
}
