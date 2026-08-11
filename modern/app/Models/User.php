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

#[Fillable(['name', 'email', 'password', 'role'])]
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
            'role' => UserRole::class,
        ];
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withPivot('role')->withTimestamps();
    }

    public function ownedBusinesses(): BelongsToMany
    {
        return $this->businesses()->wherePivot('role', 'owner');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function isRoot(): bool
    {
        return $this->role === UserRole::Root;
    }

    public function isOwner(Business $business): bool
    {
        return $this->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }
}
