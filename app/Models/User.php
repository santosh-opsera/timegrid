<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'username', 'password', 'role', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    public function ownedBusinesses(): BelongsToMany
    {
        return $this->businesses();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function getRoleAttribute(mixed $value): UserRole
    {
        if (is_string($value) && $value !== '') {
            return UserRole::tryFrom($value) ?? UserRole::Customer;
        }

        if ($this->relationLoaded('businesses')) {
            return $this->businesses->isNotEmpty() ? UserRole::Owner : UserRole::Customer;
        }

        return $this->businesses()->exists() ? UserRole::Owner : UserRole::Customer;
    }

    public function isRoot(): bool
    {
        return $this->role === UserRole::Root;
    }

    public function isOwner(Business $business): bool
    {
        return $this->businesses()
            ->where('businesses.id', $business->id)
            ->exists();
    }
}
