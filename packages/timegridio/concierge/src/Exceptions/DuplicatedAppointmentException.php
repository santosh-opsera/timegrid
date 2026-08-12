<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Exceptions;

use Exception;

class DuplicatedAppointmentException extends Exception
{
    public function __construct(string $code = '', int $previousCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Duplicated appointment: {$code}", $previousCode, $previous);
    }
}
