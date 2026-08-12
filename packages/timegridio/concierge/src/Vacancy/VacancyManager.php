<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Vacancy;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class VacancyManager
{
    protected ?VacancyTemplateBuilder $builder = null;

    public function __construct(
        protected readonly Business $business,
    ) {}

    public function builder(): VacancyTemplateBuilder
    {
        if ($this->builder === null) {
            $this->builder = new VacancyTemplateBuilder;
        }

        return $this->builder;
    }

    /**
     * @param  list<array<string, mixed>>  $parsedStatements
     */
    public function updateBatch(Business $business, array $parsedStatements): bool
    {
        $changed = false;
        $dates = $this->arrayGroupBy('date', $parsedStatements);

        foreach ($dates as $date => $statements) {
            $services = $this->arrayGroupBy('service', $statements);
            $changed = $changed || $this->processServiceStatements($business, (string) $date, $services);
        }

        return $changed;
    }

    public function unpublish(): mixed
    {
        return $this->business->vacancies()->delete();
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $services
     */
    protected function processServiceStatements(Business $business, string $date, array $services): bool
    {
        $changed = false;

        foreach ($services as $serviceSlug => $statements) {
            $service = $business->services()->where('slug', $serviceSlug)->first();

            if ($service === null) {
                continue;
            }

            $business->vacancies()
                ->forDate(Carbon::parse($date))
                ->forService($service->id)
                ->delete();

            $changed = $changed || $this->processStatements($business, $date, $service, $statements);
        }

        return $changed;
    }

    /**
     * @param  list<array<string, mixed>>  $statements
     */
    protected function processStatements(Business $business, string $date, Service $service, array $statements): bool
    {
        $changed = false;

        foreach ($statements as $statement) {
            $changed = $changed || $this->publishVacancy($business, $date, $service, $statement);
        }

        return $changed;
    }

    /**
     * @param  array<string, mixed>  $statement
     */
    protected function publishVacancy(Business $business, string $date, Service $service, array $statement): bool
    {
        $date = (string) $statement['date'];
        $startAt = Carbon::parse("{$date} {$statement['startAt']} {$business->timezone}")->timezone('UTC');
        $finishAt = Carbon::parse("{$date} {$statement['finishAt']} {$business->timezone}")->timezone('UTC');

        $vacancyValues = [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'date' => $statement['date'],
            'start_at' => $startAt,
            'finish_at' => $finishAt,
        ];

        if (! is_numeric($statement['capacity'])) {
            $humanresource = $business->humanresources()
                ->where('slug', $statement['capacity'])
                ->first();

            if ($humanresource !== null) {
                $vacancyValues['humanresource_id'] = $humanresource->id;
            }
        } else {
            $vacancyValues['capacity'] = (int) $statement['capacity'];
        }

        return Vacancy::query()->create($vacancyValues) !== null;
    }

    /**
     * @param  list<array<string, mixed>>  $array
     * @return array<string, list<array<string, mixed>>>
     */
    protected function arrayGroupBy(string $key, array $array): array
    {
        $grouped = [];

        foreach ($array as $item) {
            $grouped[$item[$key]] ??= [];
            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    public function publish(
        string $date,
        Carbon $startAt,
        Carbon $finishAt,
        int $serviceId,
        int $capacity = 1,
    ): Vacancy {
        return Vacancy::query()->updateOrCreate(
            [
                'business_id' => $this->business->id,
                'service_id' => $serviceId,
                'date' => $date,
            ],
            [
                'capacity' => $capacity,
                'start_at' => $startAt->timezone('UTC')->toDateTimeString(),
                'finish_at' => $finishAt->timezone('UTC')->toDateTimeString(),
            ]
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public function generateAvailability(string $startDate = 'today', int $futureDays = 10): array
    {
        $start = Carbon::parse($startDate);
        $end = $start->copy()->addDays($futureDays);

        $vacancies = $this->business->vacancies()
            ->with('service')
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->toDateString())
            ->orderBy('date')
            ->orderBy('start_at')
            ->get();

        $dates = [];

        for ($i = 0; $i < $futureDays; $i++) {
            $dates[$start->copy()->addDays($i)->toDateString()] = [];
        }

        foreach ($vacancies as $vacancy) {
            $dateKey = $vacancy->date instanceof Carbon
                ? $vacancy->date->toDateString()
                : (string) $vacancy->date;

            if (! array_key_exists($dateKey, $dates)) {
                continue;
            }

            $rawStart = $vacancy->getRawOriginal('start_at');
            $rawEnd = $vacancy->getRawOriginal('finish_at');

            $slotStart = Carbon::parse($rawStart);
            $slotEnd = Carbon::parse($rawEnd);

            $duration = max((int) ($vacancy->service?->duration ?? 30), 15);

            $cursor = $slotStart->copy();
            while ($cursor->copy()->addMinutes($duration)->lte($slotEnd)) {
                $timeStr = $cursor->format('H:i');
                if (! in_array($timeStr, $dates[$dateKey], true)) {
                    $dates[$dateKey][] = $timeStr;
                }
                $cursor->addMinutes($duration);
            }
        }

        foreach ($dates as $dateKey => $slots) {
            if ($slots === []) {
                unset($dates[$dateKey]);
            } else {
                sort($dates[$dateKey]);
            }
        }

        return $dates;
    }
}
