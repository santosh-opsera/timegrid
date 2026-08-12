<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableStrategy
{
    protected TimetableStrategyInterface $strategy;

    public function __construct(string $strategyId)
    {
        $this->strategy = match ($strategyId) {
            'timeslot' => new TimetableTimeslotStrategy(new Timetable),
            'dateslot' => new TimetableDateslotStrategy(new Timetable),
            default => throw new StrategyNotRecognizedException($strategyId),
        };
    }

    /**
     * @param  iterable<int, \Timegridio\Concierge\Models\Vacancy>  $vacancies
     * @return array<string, mixed>
     */
    public function buildTimetable(iterable $vacancies, string $starting = 'today', int $days = 1): array
    {
        return $this->strategy->buildTimetable($vacancies, $starting, $days);
    }
}
