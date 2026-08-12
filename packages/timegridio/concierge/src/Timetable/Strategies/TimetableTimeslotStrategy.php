<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable\Strategies;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Timetable\Timetable;

class TimetableTimeslotStrategy extends BaseTimetableStrategy implements TimetableStrategyInterface
{
    private int $interval = 30;

    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    protected function initTimetable(string $starting, int $days): void
    {
        $this->timetable
            ->interval($this->interval)
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
            $this->updateTimeslots($vacancy, $this->interval);
        }

        return $this->timetable->get();
    }

    protected function updateTimeslots(Vacancy $vacancy, int $step = 30): void
    {
        $fromTime = $vacancy->start_at->copy()->timezone('UTC');
        $toTime = $fromTime->copy();
        $limit = $vacancy->finish_at;

        while ($fromTime <= $limit) {
            $toTime->addMinutes($step);

            $capacity = $vacancy->getAvailableCapacityBetween($fromTime, $toTime);
            $time = $fromTime->timezone($vacancy->business->timezone)->format('H:i:s');

            $dateKey = $vacancy->date instanceof Carbon
                ? $vacancy->date->toDateString()
                : (string) $vacancy->date;

            $this->timetable->capacity($dateKey, $time, $vacancy->service->slug, $capacity);

            $fromTime->addMinutes($step);
        }
    }
}
