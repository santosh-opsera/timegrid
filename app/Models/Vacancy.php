<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'service_id',
        'humanresource_id',
        'date',
        'start_at',
        'finish_at',
        'capacity',
    ];

    protected $appends = ['start_time', 'end_time', 'staff_id'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
        ];
    }

    public function getStartTimeAttribute(): string
    {
        if ($this->start_at) {
            return Carbon::parse($this->start_at)->format('H:i:s');
        }

        return '09:00:00';
    }

    public function getEndTimeAttribute(): string
    {
        if ($this->finish_at) {
            return Carbon::parse($this->finish_at)->format('H:i:s');
        }

        return '17:00:00';
    }

    public function getStaffIdAttribute(): ?int
    {
        return $this->humanresource_id;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'humanresource_id');
    }
}
