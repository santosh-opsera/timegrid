<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Humanresource extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'capacity',
        'contact_id',
        'business_id',
        'calendar_link',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function setCalendarLinkAttribute(?string $calendarLink): void
    {
        $this->attributes['calendar_link'] = $calendarLink !== null && trim($calendarLink) !== ''
            ? trim($calendarLink)
            : null;
    }

    protected static function booted(): void
    {
        static::saving(function (self $humanresource): void {
            if (isset($humanresource->attributes['name'])) {
                $humanresource->attributes['slug'] = Str::slug($humanresource->attributes['name']);
            }
        });
    }
}
