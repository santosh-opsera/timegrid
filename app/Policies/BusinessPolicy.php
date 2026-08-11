<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Business $business): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role !== \App\Enums\UserRole::Customer;
    }

    public function update(User $user, Business $business): bool
    {
        return $user->isOwner($business);
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->isOwner($business);
    }

    public function manage(User $user, Business $business): bool
    {
        return $user->isOwner($business);
    }
}
