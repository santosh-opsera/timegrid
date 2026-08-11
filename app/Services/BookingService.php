<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Contact;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingService
{
    public function book(Business $business, Service $service, Contact $contact, string $date, string $time, ?string $comments = null): Appointment
    {
        $startAt = Carbon::parse("{$date} {$time}", $business->timezone);
        $endAt = $startAt->copy()->addMinutes($service->duration);

        $exists = Appointment::where('business_id', $business->id)
            ->where('service_id', $service->id)
            ->where('start_at', $startAt)
            ->whereNotIn('status', [AppointmentStatus::Canceled->value])
            ->exists();

        if ($exists) {
            throw new \RuntimeException('This time slot is already booked.');
        }

        return Appointment::create([
            'business_id' => $business->id,
            'service_id' => $service->id,
            'contact_id' => $contact->id,
            'status' => AppointmentStatus::Reserved,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration' => $service->duration,
            'comments' => $comments,
            'hash' => Str::random(32),
        ]);
    }

    private const VALID_TRANSITIONS = [
        'reserved' => ['confirmed', 'canceled'],
        'confirmed' => ['served', 'canceled'],
        'canceled' => [],
        'served' => [],
    ];

    public function confirm(Appointment $appointment): Appointment
    {
        $this->assertTransition($appointment, AppointmentStatus::Confirmed);
        $appointment->update(['status' => AppointmentStatus::Confirmed]);

        return $appointment->fresh();
    }

    public function cancel(Appointment $appointment): Appointment
    {
        $this->assertTransition($appointment, AppointmentStatus::Canceled);
        $appointment->update(['status' => AppointmentStatus::Canceled]);

        return $appointment->fresh();
    }

    public function serve(Appointment $appointment): Appointment
    {
        $this->assertTransition($appointment, AppointmentStatus::Served);
        $appointment->update(['status' => AppointmentStatus::Served]);

        return $appointment->fresh();
    }

    private function assertTransition(Appointment $appointment, AppointmentStatus $target): void
    {
        $current = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        $allowed = self::VALID_TRANSITIONS[$current] ?? [];

        if (!in_array($target->value, $allowed, true)) {
            throw new \RuntimeException("Cannot transition from '{$current}' to '{$target->value}'.");
        }
    }
}
