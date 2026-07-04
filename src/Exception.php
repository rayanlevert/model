<?php

namespace RayanLevert\Model;

/** All exceptions thrown in RayanLevert\Model\* will use this class */
class Exception extends \Exception
{
    /** String (PDOException) or integer (Exception) code */
    public readonly int|string $realCode;

    public function __construct(string $message = '', int|string $code = 0, ?\Throwable $previous = null)
    {
        $this->realCode = $code;

        parent::__construct($message, (int) $code, $previous);
    }
}
