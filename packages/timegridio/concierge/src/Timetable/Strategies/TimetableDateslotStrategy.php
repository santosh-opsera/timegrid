<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable\Strategies;

use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableDateslotStrategy extends BaseTimetableStrategy implements TimetableStrategyInterface
{
    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    protected function initTimetable(string $starting, int $days): void
    {
        $this->timetable
            ->format('date.service.time')
            ->from($starting)
            ->future($days)
            ->init();
    }

    /**
     * @param  iterable<int, Vacancy>  $vacancies
     * @return array<string, mixed>
     */
    public function buildTimetable(iterable $vacancies, string $starting = 'today', int $days = 1): array
    {
        $this->initTimetable($starting, $days);

        foreach ($vacancies as $vacancy) {
            $this->updateTimeslots($vacancy);
        }

        return $this->timetable->get();
    }

    protected function updateTimeslots(Vacancy $vacancy): void
    {
        $fromTime = $vacancy->start_at;
        $toTime = $vacancy->finish_at;

        $capacity = $vacancy->getAvailableCapacityBetween(
            $fromTime->copy()->timezone('UTC'),
            $toTime->copy()->timezone('UTC')
        );

        $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

        $dateKey = $vacancy->date instanceof \Carbon\Carbon
            ? $vacancy->date->toDateString()
            : (string) $vacancy->date;

        $this->timetable->capacity($dateKey, $time, $vacancy->service->slug, $capacity);
    }
}
