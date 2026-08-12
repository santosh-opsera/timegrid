<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceType extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'business_id',
        'slug',
        'name',
        'description',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'type_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $serviceType): void {
            if (isset($serviceType->attributes['name'])) {
                $serviceType->attributes['slug'] = Str::slug($serviceType->attributes['name']);
            }
        });
    }
}
