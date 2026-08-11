<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use App\Models\Vacancy;
use Carbon\Carbon;

class AvailabilityService
{
    protected SlotGenerator $slotGenerator;

    public function __construct(SlotGenerator $slotGenerator)
    {
        $this->slotGenerator = $slotGenerator;
    }

    public function getAvailableDates(Business $business, Service $service): array
    {
        return Vacancy::where('business_id', $business->id)
            ->where('service_id', $service->id)
            ->where('date', '>=', Carbon::today($business->timezone))
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();
    }

    public function getAvailableTimes(Business $business, Service $service, string $date): array
    {
        $vacancies = Vacancy::where('business_id', $business->id)
            ->where('service_id', $service->id)
            ->whereDate('date', $date)
            ->get();

        $allSlots = [];
        foreach ($vacancies as $vacancy) {
            $slots = $this->slotGenerator->generate($vacancy, $service->duration);
            $allSlots = array_merge($allSlots, $slots);
        }

        $booked = Appointment::where('business_id', $business->id)
            ->where('service_id', $service->id)
            ->whereDate('start_at', $date)
            ->whereNotIn('status', [AppointmentStatus::Canceled->value])
            ->pluck('start_at')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        return array_values(array_diff($allSlots, $booked));
    }
}
