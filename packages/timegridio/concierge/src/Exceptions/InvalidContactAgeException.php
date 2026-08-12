<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Exceptions;

use Exception;

class InvalidContactAgeException extends Exception
{
    public function __construct(string $message = 'Contact birthdate is in the future.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
