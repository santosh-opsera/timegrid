<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'duration',
        'color',
    ];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute(): bool
    {
        return true;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
