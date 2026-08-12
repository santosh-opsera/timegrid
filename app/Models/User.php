<?php

namespace App\Models;

use App\Traits\HasRoles;
use App\Traits\Preferenceable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string|null $last_ip
 * @property Carbon|null $last_login_at
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, Business> $businesses
 * @property Collection<int, Contact> $contacts
 * @property Collection<int, Appointment> $appointments
 * @property Collection<int, Role> $roles
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, Preferenceable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'last_ip',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_ip',
        'last_login_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Businesses owned by this user.
     *
     * @return BelongsToMany<Business, $this>
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    /**
     * Contact profiles for different businesses.
     *
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Appointments held through contact profiles.
     *
     * @return HasManyThrough<Appointment, Contact, $this>
     */
    public function appointments(): HasManyThrough
    {
        return $this->hasManyThrough(Appointment::class, Contact::class);
    }

    /**
     * Determine whether the user owns the given business.
     */
    public function isOwnerOf(int $businessId): bool
    {
        return $this->businesses()->withTrashed()->get()->contains($businessId);
    }

    /**
     * Determine whether the user owns at least one business.
     */
    public function hasBusiness(): bool
    {
        return $this->businesses->count() > 0;
    }

    /**
     * Determine whether the user has at least one contact profile.
     */
    public function hasContacts(): bool
    {
        return $this->contacts->count() > 0;
    }

    /**
     * Get the contact subscribed to the given business.
     */
    public function getContactSubscribedTo(int $businessId): ?Contact
    {
        return $this->contacts->filter(function (Contact $contact) use ($businessId) {
            return $contact->isSubscribedTo($businessId);
        })->first();
    }

    /**
     * Normalize the username before persistence.
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (?string $username) => ($username = strtolower(trim((string) $username))) === ''
                ? md5(time().uniqid())
                : $username,
        );
    }

    /**
     * Normalize the display name before persistence.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $name) => ucwords(strtolower($name)),
        );
    }
}
