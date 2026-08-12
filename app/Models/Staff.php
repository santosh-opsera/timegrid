<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'humanresources';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'capacity',
        'contact_id',
        'calendar_link',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'humanresource_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'humanresource_id');
    }
}
