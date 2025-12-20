<?php

namespace RayanLevert\Model;

/** All exceptions thrown in RayanLevert\Model\* will use this class */
class Exception extends \Exception
{
    protected string|int $realCode;

    public function __construct(string $message = '', string|int $code = 0, ?\Throwable $previous = null)
    {
        $this->realCode = $code;

        parent::__construct($message, (int) $code, $previous);
    }

    /**
     * Returns the real code of the exception (string from PDOException, int from Exception)
     *
     * @return string|int The real code of the exception
     */
    public function getRealCode(): string|int
    {
        return $this->realCode;
    }
}
