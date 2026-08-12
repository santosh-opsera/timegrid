<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Vacancy;

use Carbon\Carbon;

class VacancyParser
{
    private const REGEX_PATTERN_VACANCY = '/(?P<services>.*)\n\ (?P<days>.*)\n\ \ (?<hours>.*)/im';

    /**
     * @return list<array<string, string>>
     */
    public function readVacancies(string $vacancyString): array
    {
        preg_match_all(self::REGEX_PATTERN_VACANCY, $vacancyString, $matches, PREG_SET_ORDER);

        return $matches;
    }

    /**
     * @param  array{services: string, days: string, hours: string}  $vacancyParameters
     * @return array<string, array<string, mixed>>
     */
    public function buildVacancies(array $vacancyParameters): array
    {
        $services = $this->services($vacancyParameters['services']);
        $days = $this->dates($vacancyParameters['days']);
        $hourRanges = $this->hours($vacancyParameters['hours']);

        $builtVacancies = [];

        foreach ($services as $service) {
            foreach ($days as $day) {
                foreach ($hourRanges as $hourRange) {
                    $data = [
                        'service' => $service['slug'],
                        'date' => $day,
                        'startAt' => $hourRange['startAt'],
                        'finishAt' => $hourRange['finishAt'],
                        'capacity' => $service['capacity'],
                    ];
                    $key = md5(implode('.', $data));
                    $builtVacancies[$key] = $data;
                }
            }
        }

        return $builtVacancies;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseStatements(string $stringStatements): array
    {
        $vacancyStatements = $this->readVacancies($stringStatements);
        $builtVacancies = [];

        foreach ($vacancyStatements as $vacancyStatement) {
            $builtVacancies = array_merge($builtVacancies, $this->buildVacancies($vacancyStatement));
        }

        return $builtVacancies;
    }

    /**
     * @return list<array{slug: string, capacity: string|int}>
     */
    public function services(string $services): array
    {
        return $this->getServicesCapacity($this->splitServices($services));
    }

    /**
     * @return list<string>
     */
    public function splitServices(string $services): array
    {
        return preg_split('/\ *\,\ */', $services) ?: [];
    }

    /**
     * @param  list<string>  $services
     * @return list<array{slug: string, capacity: string|int}>
     */
    public function getServicesCapacity(array $services): array
    {
        return array_map(
            fn (string $service): array => $this->getServiceCapacity($service),
            $services
        );
    }

    /**
     * @return array{slug: string, capacity: string|int}
     */
    public function getServiceCapacity(string $service): array
    {
        $capacity = 1;

        if (str_contains($service, ':')) {
            [$service, $capacity] = explode(':', $service, 2);
        }

        return ['slug' => $service, 'capacity' => trim((string) $capacity)];
    }

    /**
     * @return list<string>
     */
    public function dates(string $days): array
    {
        return $this->convertDaysToDate($this->splitDates($days));
    }

    /**
     * @return list<string>
     */
    public function splitDates(string $days): array
    {
        return preg_split('/\ *\,\ */', $days) ?: [];
    }

    /**
     * @param  list<string>  $days
     * @return list<string>
     */
    public function convertDaysToDate(array $days): array
    {
        return array_map(
            fn (string $day): string => $this->dayToDate($day),
            $days
        );
    }

    public function dayToDate(string $day): string
    {
        return Carbon::parse($day)->toDateString();
    }

    /**
     * @return list<array{startAt: string, finishAt: string}>
     */
    public function hours(string $string): array
    {
        return $this->normalizeRanges($this->splitRanges($string));
    }

    /**
     * @param  list<string>  $ranges
     * @return list<array{startAt: string, finishAt: string}>
     */
    public function normalizeRanges(array $ranges): array
    {
        $normalizedRanges = [];

        foreach ($ranges as $range) {
            [$startAt, $finishAt] = preg_split('/\ *\-\ */', $range) ?: ['', ''];
            $normalizedRanges[] = [
                'startAt' => $this->milTimeToStandard($startAt),
                'finishAt' => $this->milTimeToStandard($finishAt),
            ];
        }

        return $normalizedRanges;
    }

    /**
     * @return list<string>
     */
    public function splitRanges(string $string): array
    {
        return preg_split('/,\ */', $string) ?: [];
    }

    public function milTimeToStandard(string $militaryTime): string
    {
        $militaryTime = trim($militaryTime);

        if ($militaryTime === '') {
            return '';
        }

        if (str_contains($militaryTime, ':')) {
            return $militaryTime;
        }

        $parts = [];

        if (strlen($militaryTime) <= 2) {
            $parts = [(int) $militaryTime, '00'];
        } elseif (strlen($militaryTime) === 3) {
            $parts = [substr($militaryTime, 0, 1), substr($militaryTime, 1, 2)];
        } elseif (strlen($militaryTime) === 4) {
            $parts = [substr($militaryTime, 0, 2), substr($militaryTime, 2, 2)];
        }

        return implode(':', $parts);
    }
}
