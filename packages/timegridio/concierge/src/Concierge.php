<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Timegridio\Concierge\Booking\BookingManager;
use Timegridio\Concierge\Calendar\Calendar;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;
use Timegridio\Concierge\Timetable\Strategies\TimetableStrategy;
use Timegridio\Concierge\Vacancy\VacancyManager;

class Concierge extends Workspace
{
    protected ?TimetableStrategy $timetable = null;

    protected ?Calendar $calendar = null;

    protected ?BookingManager $booking = null;

    protected ?VacancyManager $vacancies = null;

    protected ?Appointment $appointment = null;

    protected function calendar(): Calendar
    {
        if ($this->calendar === null) {
            $this->calendar = new Calendar(
                $this->business->strategy,
                $this->business->vacancies(),
                $this->business->timezone
            );
        }

        return $this->calendar;
    }

    public function timetable(): TimetableStrategy
    {
        if ($this->timetable === null) {
            $this->timetable = new TimetableStrategy($this->business->strategy);
        }

        return $this->timetable;
    }

    public function vacancies(): ?VacancyManager
    {
        if ($this->vacancies === null && $this->business !== null) {
            $this->vacancies = new VacancyManager($this->business);
        }

        return $this->vacancies;
    }

    public function booking(): ?BookingManager
    {
        if ($this->booking === null && $this->business !== null) {
            $this->booking = new BookingManager($this->business);
        }

        return $this->booking;
    }

    /**
     * @param  array{
     *     issuer: mixed,
     *     service: Service,
     *     contact: Contact,
     *     comments?: string|null,
     *     date: string,
     *     time: string,
     *     timezone: string,
     * }  $request
     */
    public function takeReservation(array $request): Appointment|false
    {
        $issuer = $request['issuer'];
        $service = $request['service'];
        $contact = $request['contact'];
        $comments = $request['comments'] ?? null;

        $vacancies = $this->calendar()
            ->forService($service->id)
            ->withDuration($service->duration)
            ->forDate($request['date'])
            ->atTime($request['time'], $request['timezone'])
            ->find();

        if ($vacancies->count() === 0) {
            return false;
        }

        /** @var Vacancy $vacancy */
        $vacancy = $vacancies->first();

        $humanresourceId = $vacancy->humanresource?->id;

        $startAt = $this->makeDateTimeUTC($request['date'], $request['time'], $request['timezone']);
        $finishAt = $startAt->copy()->addMinutes((int) $service->duration);

        $appointment = $this->generateAppointment(
            $issuer,
            $this->business->id,
            $contact->id,
            $service->id,
            $startAt,
            $finishAt,
            $comments,
            $humanresourceId
        );

        if ($appointment->duplicates()) {
            $this->appointment = $appointment;

            throw new DuplicatedAppointmentException($appointment->code);
        }

        $appointment->vacancy()->associate($vacancy);
        $appointment->save();

        return $appointment;
    }

    protected function generateAppointment(
        mixed $issuerId,
        int $businessId,
        int $contactId,
        int $serviceId,
        Carbon $startAt,
        Carbon $finishAt,
        ?string $comments = null,
        ?int $humanresourceId = null,
    ): Appointment {
        $appointment = new Appointment;

        $appointment->doReserve();
        $appointment->setStartAtAttribute($startAt);
        $appointment->setFinishAtAttribute($finishAt);
        $appointment->business()->associate($businessId);
        $appointment->issuer()->associate($issuerId);
        $appointment->contact()->associate($contactId);
        $appointment->service()->associate($serviceId);
        $appointment->humanresource()->associate($humanresourceId);
        $appointment->comments = $comments;
        $appointment->doHash();

        return $appointment;
    }

    public function isBookable(string $fromDate = 'now', int $days = 7): bool
    {
        $fromDate = Carbon::parse($fromDate)->timezone($this->business->timezone);

        $count = $this->business
            ->vacancies()
            ->future($fromDate)
            ->until($fromDate->copy()->addDays($days))
            ->count();

        return $count > 0;
    }

    public function getActiveAppointments(): Collection
    {
        return $this->business
            ->bookings()
            ->with(['contact', 'business', 'service'])
            ->active()
            ->orderBy('start_at')
            ->get();
    }

    public function getUnservedAppointments(): Collection
    {
        return $this->business
            ->bookings()
            ->with(['contact', 'business', 'service'])
            ->unserved()
            ->orderBy('start_at')
            ->get();
    }

    public function getUnarchivedAppointments(): Collection
    {
        return $this->business
            ->bookings()
            ->with(['contact', 'business', 'service'])
            ->unarchived()
            ->orderBy('start_at')
            ->get();
    }

    protected function makeDateTime(string $date, string $time, ?string $timezone = null): Carbon
    {
        return Carbon::parse("{$date} {$time} ".($timezone ?? 'UTC'));
    }

    protected function makeDateTimeUTC(string $date, string $time, ?string $timezone = null): Carbon
    {
        return $this->makeDateTime($date, $time, $timezone)->timezone('UTC');
    }

    public function appointment(): ?Appointment
    {
        return $this->appointment;
    }
}
