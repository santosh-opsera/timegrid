<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Timegridio\Concierge\Models\Domain;

trait IsIntoDomain
{
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
