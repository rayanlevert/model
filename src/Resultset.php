<?php

namespace RayanLevert\Model;

use Countable;
use PDO;
use PDOStatement;

/**
 * @template T of Model
 */
class Resultset implements \IteratorAggregate, Countable
{
    /** @param T $model */
    public function __construct(protected Model $model, protected PDOStatement $statement)
    {
    }

    /** @return ?T */
    public function current(): ?Model
    {
        if (!$aResults = $this->statement->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        return clone $this->model->assign($aResults);
    }

    public function getIterator(): \Traversable
    {
        return $this->statement->getIterator();
    }

    public function count(): int
    {
        return $this->statement->rowCount();
    }
}
