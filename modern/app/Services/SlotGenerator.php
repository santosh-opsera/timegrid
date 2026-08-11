<?php

namespace App\Services;

use App\Models\Vacancy;
use Carbon\Carbon;

class SlotGenerator
{
    public function generate(Vacancy $vacancy, int $duration): array
    {
        $slots = [];
        $start = Carbon::parse($vacancy->date->format('Y-m-d') . ' ' . $vacancy->start_time);
        $end = Carbon::parse($vacancy->date->format('Y-m-d') . ' ' . $vacancy->end_time);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        return $slots;
    }
}
