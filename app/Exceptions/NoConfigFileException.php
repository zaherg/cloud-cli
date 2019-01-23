<?php

namespace App\Exceptions;

use Throwable;
use Symfony\Component\Console\Exception\RuntimeException;

class NoConfigFileException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
