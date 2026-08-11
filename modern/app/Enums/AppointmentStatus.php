<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Reserved = 'reserved';
    case Confirmed = 'confirmed';
    case Canceled = 'canceled';
    case Served = 'served';
}
