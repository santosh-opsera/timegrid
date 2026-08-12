<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Timetable\Timetable;

interface TimetableStrategyInterface
{
    public function __construct(Timetable $timetable);

    /**
     * @param  iterable<int, \Timegridio\Concierge\Models\Vacancy>  $vacancies
     * @return array<string, mixed>
     */
    public function buildTimetable(iterable $vacancies, string $starting = 'today', int $days = 1): array;
}
