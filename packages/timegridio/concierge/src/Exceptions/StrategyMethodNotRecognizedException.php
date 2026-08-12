<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Exceptions;

use Exception;

class StrategyMethodNotRecognizedException extends Exception
{
    public function __construct(string $method = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Strategy method not recognized: {$method}", $code, $previous);
    }
}
