<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class Repository
{
    private string $identificator = 'slug';

    public function getBusiness(int|string $id, ?string $identificator = null): ?Business
    {
        $identificator ??= $this->identificator;

        return Business::query()->where($identificator, $id)->first();
    }

    public function getService(Business $business, int|string $id, ?string $identificator = null): ?Service
    {
        $identificator ??= $this->identificator;

        return $business->services()->where($identificator, $id)->first();
    }
}
