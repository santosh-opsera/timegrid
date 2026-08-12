<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'issuer_id',
        'business_id',
        'service_id',
        'contact_id',
        'humanresource_id',
        'vacancy_id',
        'status',
        'start_at',
        'finish_at',
        'duration',
        'comments',
        'hash',
    ];

    protected $appends = ['end_at', 'status_label'];

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
        ];
    }

    public function getEndAtAttribute(): mixed
    {
        return $this->finish_at;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status instanceof AppointmentStatus) {
            return $this->status->label();
        }

        return (string) $this->status;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'humanresource_id');
    }
}
