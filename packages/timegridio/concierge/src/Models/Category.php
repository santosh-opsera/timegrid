<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'strategy',
    ];

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
