<?php

namespace RayanLevert\Model\Queries;

use RayanLevert\Model\Exception;
use RayanLevert\Model\Model;

/** Queries for PostgreSQL databases */
class Postgres extends Rdbm
{
    public string $escapeCharacter = '"';
}
