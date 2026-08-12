<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Timetable;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class Timetable
{
    /** @var array<string, mixed>|null */
    protected ?array $timetable = null;

    protected string $from = 'today';

    protected int $future = 1;

    protected string $startAt = '09:00:00';

    protected string $finishAt = '18:00:00';

    protected int $interval = 30;

    /** @var list<string> */
    protected array $services = [];

    /** @var list<string> */
    protected array $dimensions = ['date', 'service', 'time'];

    public function from(string $relative): static
    {
        $this->from = $relative;

        return $this;
    }

    public function future(int $days): static
    {
        $this->future = $days;

        return $this;
    }

    public function startAt(string $time): static
    {
        $this->startAt = $time;

        return $this;
    }

    public function finishAt(string $time): static
    {
        $this->finishAt = $time;

        return $this;
    }

    public function interval(int $interval = 30): static
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @param  list<string>  $services
     */
    public function services(array $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function init(): static
    {
        $this->timetable = [];

        $dimensions = [
            'service' => $this->inflateServices(),
            'date' => $this->inflateDates(),
            'time' => $this->inflateTimes(),
        ];

        foreach ($dimensions['service'] as $service) {
            foreach ($dimensions['date'] as $date) {
                foreach ($dimensions['time'] as $time) {
                    $this->capacity($date, $time, $service, 0);
                }
            }
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function inflateServices(): array
    {
        return $this->services;
    }

    /**
     * @return array<string, string>
     */
    public function inflateDates(): array
    {
        $dates = [];

        for ($i = 0; $i < $this->future; $i++) {
            $date = Carbon::parse("{$this->from} +{$i} days")->toDateString();
            $dates[$date] = $date;
        }

        return $dates;
    }

    /**
     * @return array<string, string>
     */
    public function inflateTimes(): array
    {
        $start = Carbon::parse('today '.$this->startAt);
        $finish = Carbon::parse('today '.$this->finishAt);
        $times = [];

        for ($current = $start->copy(); $current->lt($finish); $current->addMinutes($this->interval)) {
            $time = $current->format('H:i:s');
            $times[$time] = $time;
        }

        return $times;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        if ($this->timetable === null) {
            $this->init();
        }

        return $this->timetable;
    }

    public function capacity(string $date, string $time, string $service, ?int $capacity = null): mixed
    {
        $path = $this->dimensions(compact('date', 'service', 'time'));

        if ($capacity === null) {
            return Arr::get($this->timetable ?? [], $path);
        }

        Arr::set($this->timetable, $path, $capacity);

        return $capacity;
    }

    /**
     * @param  array<string, string>  $segments
     */
    private function dimensions(array $segments): string
    {
        $translatedDimensions = $this->dimensions;
        $this->arraySubstitute($translatedDimensions, $segments);

        return implode('.', $translatedDimensions);
    }

    /**
     * @param  list<string>|string  $dimensions
     */
    public function format(array|string $dimensions): static
    {
        if (is_array($dimensions)) {
            $this->dimensions = $dimensions;
        } else {
            $this->dimensions = explode('.', $dimensions);
        }

        return $this;
    }

    /**
     * @param  list<string>  $array1
     * @param  array<string, string>  $array2
     */
    private function arraySubstitute(array &$array1, array $array2): void
    {
        foreach ($array1 as $key => $value) {
            $array1[$key] = $array2[$value];
        }
    }
}
