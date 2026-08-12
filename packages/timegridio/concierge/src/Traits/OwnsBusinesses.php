<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Timegridio\Concierge\Models\Business;

trait OwnsBusinesses
{
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }
}
