<?php

namespace RayanLevert\Model\Queries;

/** Queries for SQLite databases */
class Sqlite extends RDBM
{
    public string $escapeCharacter = '"';
}
