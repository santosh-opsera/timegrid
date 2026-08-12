<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Contact extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'nin',
        'gender',
        'firstname',
        'lastname',
        'occupation',
        'martial_status',
        'postal_address',
        'birthdate',
        'mobile',
        'mobile_country',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function hasAppointment(): bool
    {
        return $this->appointments_count > 0;
    }

    public function appointmentsCount(): HasMany
    {
        return $this
            ->hasMany(Appointment::class)
            ->selectRaw('contact_id, count(*) as aggregate')
            ->groupBy('contact_id');
    }

    public function getAppointmentsCountAttribute(): int
    {
        if (! array_key_exists('appointmentsCount', $this->relations)) {
            $this->load('appointmentsCount');
        }

        $related = $this->getRelation('appointmentsCount');

        return ($related->count() > 0) ? (int) $related->first()->aggregate : 0;
    }

    public function setMobileAttribute(?string $mobile): void
    {
        $this->attributes['mobile'] = $mobile !== null && trim($mobile) !== ''
            ? trim($mobile)
            : null;
    }

    public function setMobileCountryAttribute(?string $country): void
    {
        $this->attributes['mobile_country'] = $country !== null && trim($country) !== ''
            ? trim($country)
            : null;
    }

    public function setBirthdateAttribute(Carbon|string|null $birthdate): void
    {
        if ($birthdate === null) {
            $this->attributes['birthdate'] = null;

            return;
        }

        $this->attributes['birthdate'] = $birthdate instanceof Carbon
            ? $birthdate
            : Carbon::parse($birthdate);
    }

    public function setEmailAttribute(?string $email): void
    {
        $this->attributes['email'] = ($email === null || trim($email) === '') ? null : $email;
    }

    public function setNinAttribute(?string $nin): void
    {
        $this->attributes['nin'] = ($nin === null || trim($nin) === '') ? null : $nin;
    }

    public function getEmailAttribute(): ?string
    {
        if ($email = Arr::get($this->attributes, 'email')) {
            return $email;
        }

        return $this->user?->email;
    }

    public function isSubscribedTo(int $businessId): bool
    {
        return $this->businesses->contains($businessId);
    }

    public function isProfileOf(int $userId): bool
    {
        return $this->user !== null && (int) $this->user->id === $userId;
    }
}
