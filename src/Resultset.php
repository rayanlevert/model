<?php

namespace RayanLevert\Model;

use PDO;

/**
 * Lazy loading resultset creating one instance of the provided Model for each row
 *
 * @template T of Model
 */
class Resultset implements \Countable, \Iterator
{
    private array|false $currentRow = false;
    private int $position = 0;

    /** Whether the statement has been consumed */
    private bool $statementPrimed = false;

    /** @param T $model */
    public function __construct(protected Model $model, protected \PDOStatement $statement)
    {
    }

    /** @return ?T */
    public function current(): ?Model
    {
        if (!$this->currentRow) {
            return null;
        }

        return clone $this->model->assign($this->currentRow);
    }

    public function next(): void
    {
        $this->currentRow      = $this->statement->fetch(PDO::FETCH_ASSOC);
        $this->statementPrimed = true;

        if ($this->currentRow) {
            $this->position++;
        }
    }

    /**
     * Fetches the first model from the resultset (mostly for findFirst* methods)
     *
     * @throws Exception If the resultset has already been consumed
     *
     * @return ?T
     */
    public function first(): ?Model
    {
        if ($this->position || $this->statementPrimed) {
            throw new Exception('Cannot fetch the first model if the resultset has already been looped');
        }

        if (!$this->currentRow) {
            $this->next();
        }

        return $this->current();
    }

    public function key(): int
    {
        return $this->position;
    }

    /**
     * Primes the first row for the Iterator
     *
     * Once the PDO cursor is consumed, it cannot seek back; further calls only reset.
     */
    public function rewind(): void
    {
        if ($this->statementPrimed) {
            $this->position = 0;

            return;
        }

        $this->position        = 0;
        $this->currentRow      = $this->statement->fetch(PDO::FETCH_ASSOC);
        $this->statementPrimed = true;
    }

    public function valid(): bool
    {
        return $this->currentRow !== false;
    }

    public function count(): int
    {
        return $this->statement->rowCount();
    }
}
