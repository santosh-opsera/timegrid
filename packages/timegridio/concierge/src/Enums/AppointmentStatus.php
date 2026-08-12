<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Enums;

enum AppointmentStatus: string
{
    case Reserved = 'R';
    case Confirmed = 'C';
    case Annulated = 'A';
    case Served = 'S';

    public function label(): string
    {
        return match ($this) {
            self::Reserved => 'reserved',
            self::Confirmed => 'confirmed',
            self::Annulated => 'canceled',
            self::Served => 'served',
        };
    }

    public function isCancellable(): bool
    {
        return $this->isActive();
    }

    public function isActive(): bool
    {
        return $this === self::Reserved || $this === self::Confirmed;
    }
}
