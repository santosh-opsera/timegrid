<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Calendar;

use Illuminate\Support\Collection;

class DateslotCalendar extends BaseCalendar
{
    public function find(): Collection
    {
        $this->prepare();

        return $this->vacancies->get()->reject(
            static fn ($vacancy): bool => ! $vacancy->hasRoom()
        );
    }

    protected function prepare(): static
    {
        if ($this->service !== null) {
            $this->vacancies->forService((int) $this->service);
        }

        if ($this->date !== null) {
            $this->vacancies->forDate($this->date());
        }

        return $this;
    }
}
