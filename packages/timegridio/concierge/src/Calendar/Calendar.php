<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Calendar;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Timegridio\Concierge\Exceptions\StrategyMethodNotRecognizedException;
use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;

class Calendar
{
    protected BaseCalendar $strategy;

    public function __construct(
        string $strategyName,
        HasMany $vacancies,
        ?string $timezone = null,
    ) {
        $this->strategy = match (strtolower($strategyName)) {
            'timeslot' => new TimeslotCalendar($vacancies, $timezone),
            'dateslot' => new DateslotCalendar($vacancies, $timezone),
            default => throw new StrategyNotRecognizedException($strategyName),
        };
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (! method_exists($this->strategy, $name)) {
            throw new StrategyMethodNotRecognizedException($name);
        }

        return $this->strategy->{$name}(...$arguments);
    }
}
