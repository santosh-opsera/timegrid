<?php

namespace App\Enums;

enum BookingStrategy: string
{
    case Timeslot = 'timeslot';
    case Dateslot = 'dateslot';
}
