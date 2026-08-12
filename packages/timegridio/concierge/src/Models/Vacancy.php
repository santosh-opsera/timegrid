<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'date',
        'start_at',
        'finish_at',
        'business_id',
        'service_id',
        'humanresource_id',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function humanresource(): BelongsTo
    {
        return $this->belongsTo(Humanresource::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function humanresourceSlug(): string
    {
        return $this->humanresource_id !== null
            ? (string) $this->humanresource?->slug
            : '';
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('date', $date->toDateString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForDateTime(Builder $query, Carbon $datetime): Builder
    {
        return $query
            ->where('start_at', '<=', $datetime->toDateTimeString())
            ->where('finish_at', '>=', $datetime->toDateTimeString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFuture(Builder $query, ?Carbon $since = null): Builder
    {
        $since ??= Carbon::now();

        return $query->where('date', '>=', $since->toDateString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUntil(Builder $query, ?Carbon $until = null): Builder
    {
        if ($until === null) {
            return $query;
        }

        return $query->where('date', '<', $until->toDateString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForService(Builder $query, int $serviceId): Builder
    {
        return $query->where('service_id', $serviceId);
    }

    public function isHoldingAnyFor(int $userId): bool
    {
        foreach ($this->appointments as $appointment) {
            if ($appointment->contact?->isProfileOf($userId)) {
                return true;
            }
        }

        return false;
    }

    public function getCapacityAttribute(): int
    {
        if ($this->humanresource !== null) {
            return (int) $this->humanresource->capacity;
        }

        return (int) ($this->attributes['capacity'] ?? 0);
    }

    public function hasRoom(): bool
    {
        return $this->capacity > $this->appointments()->active()->count();
    }

    public function hasRoomBetween(Carbon $startAt, Carbon $finishAt): bool
    {
        return $this->capacity > $this->business
            ->bookings()
            ->active()
            ->affectingInterval($startAt, $finishAt)
            ->affectingHumanresource($this->humanresource_id)
            ->count()
            && ($this->start_at <= $startAt && $this->finish_at >= $finishAt);
    }

    public function getAvailableCapacityBetween(Carbon $startAt, Carbon $finishAt): int
    {
        if (! ($this->start_at <= $startAt && $this->finish_at >= $finishAt)) {
            return 0;
        }

        $count = $this->business
            ->bookings()
            ->active()
            ->affectingHumanresource($this->humanresource_id)
            ->affectingInterval($startAt, $finishAt)
            ->count();

        return $this->capacity - (int) $count;
    }
}
