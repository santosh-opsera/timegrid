<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'business_id',
        'slug',
        'name',
        'duration',
        'description',
        'prerequisites',
        'type_id',
        'color',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'type_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getTypeNameAttribute(): string
    {
        return $this->type?->name ?? '';
    }

    public function setDurationAttribute(mixed $duration): void
    {
        $this->attributes['duration'] = $duration !== null && $duration !== ''
            ? (int) $duration
            : null;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            if (isset($service->attributes['name'])) {
                $service->attributes['slug'] = Str::slug($service->attributes['name']);
            }
        });
    }
}
