<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Exceptions;

use Exception;

class StrategyNotRecognizedException extends Exception
{
    public function __construct(string $strategy = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Strategy not recognized: {$strategy}", $code, $previous);
    }
}
