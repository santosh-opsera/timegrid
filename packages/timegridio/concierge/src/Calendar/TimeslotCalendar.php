<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Calendar;

use Illuminate\Support\Collection;

class TimeslotCalendar extends BaseCalendar
{
    public function find(): Collection
    {
        $this->prepare();

        $results = $this->vacancies->get();
        $fromDatetime = $this->getUTCDateTime();

        if ($this->duration !== null) {
            $toDatetime = $fromDatetime->copy()->addMinutes($this->duration);

            $results = $results->reject(
                static fn ($vacancy): bool => ! $vacancy->hasRoomBetween($fromDatetime, $toDatetime)
            );
        }

        return $results;
    }

    protected function prepare(): static
    {
        if ($this->service !== null) {
            $this->vacancies->forService((int) $this->service);
        }

        if ($this->date !== null && $this->time !== null) {
            $this->vacancies->forDateTime($this->getUTCDateTime());
        }

        return $this;
    }
}
