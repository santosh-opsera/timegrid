<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Booking;

use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;

class BookingManager
{
    protected ?Appointment $appointment = null;

    public function __construct(
        protected readonly Business $business,
    ) {}

    public function appointment(string $hash): self
    {
        $this->appointment = $this->business
            ->bookings()
            ->where('hash', 'like', $hash.'%')
            ->first();

        return $this;
    }

    public function cancel(): Appointment
    {
        return $this->appointment->doCancel();
    }

    public function confirm(): Appointment
    {
        return $this->appointment->doConfirm();
    }

    public function serve(): Appointment
    {
        return $this->appointment->doServe();
    }
}
