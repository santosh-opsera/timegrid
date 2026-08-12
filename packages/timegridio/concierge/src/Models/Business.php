<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Timegridio\Concierge\Addressbook;
use Timegridio\Concierge\Traits\IsIntoDomain;
use Timegridio\Concierge\Traits\Preferenceable;

class Business extends Model
{
    use IsIntoDomain;
    use Preferenceable;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'domain_id',
        'slug',
        'name',
        'description',
        'postal_address',
        'phone',
        'social_facebook',
        'timezone',
        'strategy',
        'plan',
        'country_code',
        'locale',
        'listed',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (self $business): void {
            $business->slug = $business->makeSlug($business->name);
        });
    }

    protected function makeSlug(string $name): string
    {
        return Str::slug($name);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function addressbook(): Addressbook
    {
        return new Addressbook($this);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->with('user')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function servicetypes(): HasMany
    {
        return $this->hasMany(ServiceType::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }

    public function humanresources(): HasMany
    {
        return $this->hasMany(Humanresource::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(config('auth.providers.users.model'))->withTimestamps();
    }

    public function owner(): mixed
    {
        return $this->owners()->first();
    }

    public function subscriptionsCount(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->selectRaw('id, count(*) as aggregate')
            ->whereNotNull('user_id')
            ->groupBy('business_id');
    }

    public function getSubscriptionsCountAttribute(): int
    {
        if (! array_key_exists('subscriptionsCount', $this->relations)) {
            $this->load('subscriptionsCount');
        }

        $related = $this->getRelation('subscriptionsCount');

        return ($related->count() > 0) ? (int) $related->first()->aggregate : 0;
    }

    public function getRouteKey(): string
    {
        return (string) $this->slug;
    }

    public function setSlugAttribute(?string $slug): void
    {
        $this->attributes['slug'] = $slug ?? Str::slug((string) $this->name);
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = trim($name);
        $this->attributes['slug'] = Str::slug(trim($name));
    }

    public function setPhoneAttribute(?string $phone): void
    {
        $this->attributes['phone'] = $phone !== null && trim($phone) !== ''
            ? trim($phone)
            : null;
    }

    public function setPostalAddressAttribute(?string $postalAddress): void
    {
        $this->attributes['postal_address'] = $postalAddress !== null && trim($postalAddress) !== ''
            ? trim($postalAddress)
            : null;
    }

    public function setSocialFacebookAttribute(?string $facebookPageUrl): void
    {
        $this->attributes['social_facebook'] = $facebookPageUrl !== null && trim($facebookPageUrl) !== ''
            ? trim($facebookPageUrl)
            : null;
    }
}
