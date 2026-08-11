<?php

namespace App\Enums;

enum UserRole: string
{
    case Root = 'root';
    case Owner = 'owner';
    case Staff = 'staff';
    case Customer = 'customer';
}
