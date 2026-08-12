<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'mobile',
        'gender',
        'nin',
        'occupation',
        'postal_address',
        'notes',
    ];

    protected $appends = ['name', 'phone'];

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname}");
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->mobile;
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['mobile'] = $value;
    }
}
