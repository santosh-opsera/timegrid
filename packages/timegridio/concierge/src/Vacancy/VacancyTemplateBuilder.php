<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Vacancy;

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class VacancyTemplateBuilder
{
    /** @var list<string> */
    protected array $statement = [];

    public function getTemplate(Business $business, Service $service): string
    {
        $this->statement = [];
        $this->addServiceStatement($service);
        $this->addDaysStatement();
        $this->addTimeRangeStatement(
            (string) $business->pref('start_at'),
            (string) $business->pref('finish_at')
        );

        return $this->build();
    }

    protected function addServiceStatement(Service $service): void
    {
        $this->addStatement($service->slug.':1');
    }

    protected function addDaysStatement(): void
    {
        $this->addStatement(' mon, tue, wed, thu, fri, sat');
    }

    protected function addTimeRangeStatement(string $startAt, string $finishAt): void
    {
        $this->addStatement('  '.$startAt.' - '.$finishAt);
    }

    protected function addStatement(string $statement): void
    {
        $this->statement[] = $statement;
    }

    protected function build(): string
    {
        return implode("\n", $this->statement);
    }
}
