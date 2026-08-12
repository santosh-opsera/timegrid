<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Calendar;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

abstract class BaseCalendar
{
    protected HasMany $vacancies;

    protected int|string|null $service = null;

    protected ?int $duration = null;

    protected ?string $date = null;

    protected ?string $time = null;

    protected string $timezone = 'UTC';

    public function __construct(HasMany $vacancies, ?string $timezone = 'UTC')
    {
        $this->vacancies = $vacancies;
        $this->timezone($timezone ?? 'UTC');
    }

    public function timezone(?string $timezone = null): static
    {
        if ($timezone !== null) {
            $this->timezone = $timezone;
        }

        return $this;
    }

    public function forService(int|string|null $service = null): static
    {
        $this->service = $service;

        return $this;
    }

    public function forDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function atTime(string $time, ?string $timezone = null): static
    {
        $this->time = $time;

        if ($timezone !== null) {
            $this->timezone = $timezone;
        }

        return $this;
    }

    public function withDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getUTCDateTime(): Carbon
    {
        return Carbon::parse("{$this->date} {$this->time} {$this->timezone}")->timezone('UTC');
    }

    final protected function date(): Carbon
    {
        return Carbon::parse((string) $this->date);
    }

    abstract public function find(): Collection;

    abstract protected function prepare(): static;
}
