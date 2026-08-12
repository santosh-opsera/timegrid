<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;

abstract class BaseTimetableStrategy
{
    protected Timetable $timetable;

    abstract protected function initTimetable(string $starting, int $days): void;
}
