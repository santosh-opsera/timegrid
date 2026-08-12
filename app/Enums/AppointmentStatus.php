<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Reserved = 'R';
    case Confirmed = 'C';
    case Canceled = 'A';
    case Served = 'S';

    public function label(): string
    {
        return match ($this) {
            self::Reserved => 'reserved',
            self::Confirmed => 'confirmed',
            self::Canceled => 'canceled',
            self::Served => 'served',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'reserved', 'R' => self::Reserved,
            'confirmed', 'C' => self::Confirmed,
            'canceled', 'cancelled', 'annulated', 'A' => self::Canceled,
            'served', 'S' => self::Served,
            default => self::tryFrom($label) ?? self::Reserved,
        };
    }
}
