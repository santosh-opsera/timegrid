<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Timegridio\Concierge\Enums\AppointmentStatus;

/**
 * @property AppointmentStatus|null $status
 */
class Appointment extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'issuer_id',
        'contact_id',
        'business_id',
        'service_id',
        'humanresource_id',
        'vacancy_id',
        'hash',
        'status',
        'start_at',
        'finish_at',
        'duration',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    public function save(array $options = []): bool
    {
        $this->doHash();

        return parent::save($options);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'issuer_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function humanresource(): BelongsTo
    {
        return $this->belongsTo(Humanresource::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function user(): mixed
    {
        return $this->contact?->user;
    }

    public function duplicates(): bool
    {
        return self::query()->where('hash', $this->hash)->exists();
    }

    public function duration(): int
    {
        return (int) $this->finish_at->diffInMinutes($this->start_at);
    }

    public function getHashAttribute(): string
    {
        return isset($this->attributes['hash'])
            ? (string) $this->attributes['hash']
            : $this->doHash();
    }

    public function getFinishAtAttribute(mixed $value): Carbon
    {
        if ($value !== null) {
            return $value instanceof Carbon ? $value : Carbon::parse($value);
        }

        if (is_numeric($this->duration)) {
            return $this->start_at->copy()->addMinutes((int) $this->duration);
        }

        return $this->start_at->copy();
    }

    public function getCancellationDeadlineAttribute(): Carbon
    {
        $hours = (int) $this->business->pref('appointment_cancellation_pre_hs');

        return $this->start_at
            ->copy()
            ->subHours($hours)
            ->timezone($this->business->timezone);
    }

    public function getStatusLabelAttribute(): string
    {
        $status = $this->status;

        if ($status instanceof AppointmentStatus) {
            return $status->label();
        }

        return AppointmentStatus::tryFrom((string) $status)?->label() ?? '';
    }

    public function getDateAttribute(): string
    {
        return $this->start_at
            ->timezone($this->business->timezone)
            ->toDateString();
    }

    public function getCodeAttribute(): string
    {
        $length = (int) $this->business->pref('appointment_code_length');

        return strtoupper(substr($this->hash, 0, $length));
    }

    public function doHash(): string
    {
        $this->attributes['hash'] = md5(
            $this->start_at.'/'.
            $this->contact_id.'/'.
            $this->business_id.'/'.
            $this->service_id
        );

        return (string) $this->attributes['hash'];
    }

    public function setStartAtAttribute(Carbon $datetime): void
    {
        $this->attributes['start_at'] = $datetime;
    }

    public function setFinishAtAttribute(Carbon $datetime): void
    {
        $this->attributes['finish_at'] = $datetime;
    }

    public function setCommentsAttribute(?string $comments): void
    {
        $this->attributes['comments'] = $comments !== null && trim($comments) !== ''
            ? trim($comments)
            : null;
    }

    public function isReserved(): bool
    {
        return $this->status === AppointmentStatus::Reserved;
    }

    public function isActive(): bool
    {
        return $this->status?->isActive() ?? false;
    }

    public function isPending(): bool
    {
        return $this->isActive() && $this->isFuture();
    }

    public function isFuture(): bool
    {
        return ! $this->isDue();
    }

    public function isDue(): bool
    {
        return $this->start_at->isPast();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfBusiness(Builder $query, int $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * @param  Builder<self>  $query
     * @param  Collection<int, Contact>|iterable<int, Contact>  $contacts
     * @return Builder<self>
     */
    public function scopeForContacts(Builder $query, iterable $contacts): Builder
    {
        $ids = $contacts instanceof Collection
            ? $contacts->pluck('id')
            : collect($contacts)->pluck('id');

        return $query->whereIn('contact_id', $ids);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnarchived(Builder $query): Builder
    {
        $carbon = Carbon::parse('today midnight')->timezone('UTC');

        return $query->where(function (Builder $query) use ($carbon): void {
            $query->whereIn('status', [
                AppointmentStatus::Reserved->value,
                AppointmentStatus::Confirmed->value,
            ])
                ->where('start_at', '<=', $carbon)
                ->orWhere(function (Builder $query) use ($carbon): void {
                    $query->where('start_at', '>=', $carbon);
                });
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeServed(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::Served->value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCanceled(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::Annulated->value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnServed(Builder $query): Builder
    {
        return $query->where('status', '<>', AppointmentStatus::Served->value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            AppointmentStatus::Reserved->value,
            AppointmentStatus::Confirmed->value,
        ]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfDate(Builder $query, Carbon $date): Builder
    {
        $date = $date->copy()->timezone('UTC');

        return $query->whereRaw('date(`start_at`) = ?', [$date->toDateString()]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAffectingInterval(Builder $query, Carbon $startAt, Carbon $finishAt): Builder
    {
        $startAt = $startAt->copy()->timezone('UTC');
        $finishAt = $finishAt->copy()->timezone('UTC');

        return $query->where(function (Builder $query) use ($startAt, $finishAt): void {
            $query->where(function (Builder $query) use ($startAt, $finishAt): void {
                $query->where('finish_at', '>=', $finishAt)
                    ->where('start_at', '<', $startAt);
            })
                ->orWhere(function (Builder $query) use ($startAt, $finishAt): void {
                    $query->where('finish_at', '<=', $finishAt)
                        ->where('finish_at', '>', $startAt);
                })
                ->orWhere(function (Builder $query) use ($startAt, $finishAt): void {
                    $query->where('start_at', '>=', $startAt)
                        ->where('start_at', '<', $finishAt);
                });
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAffectingHumanresource(Builder $query, ?int $humanresourceId): Builder
    {
        if ($humanresourceId === null) {
            return $query;
        }

        return $query->where('humanresource_id', $humanresourceId);
    }

    public function isTarget(int $userId): bool
    {
        return (bool) $this->contact?->isProfileOf($userId);
    }

    public function isIssuer(int $userId): bool
    {
        return $this->issuer !== null && (int) $this->issuer->id === $userId;
    }

    public function isOwner(int $userId): bool
    {
        return $this->business->owners->contains($userId);
    }

    public function canCancel(int $userId): bool
    {
        return $this->isOwner($userId)
            || ($this->isIssuer($userId) && $this->isOnTimeToCancel())
            || ($this->isTarget($userId) && $this->isOnTimeToCancel());
    }

    public function isOnTimeToCancel(): bool
    {
        $graceHours = (int) $this->business->pref('appointment_cancellation_pre_hs');
        $diff = $this->start_at->diffInHours(Carbon::now());

        return (int) $diff >= $graceHours;
    }

    public function canServe(int $userId): bool
    {
        return $this->isOwner($userId);
    }

    public function canConfirm(int $userId): bool
    {
        return $this->isIssuer($userId) || $this->isOwner($userId);
    }

    public function isServeableBy(int $userId): bool
    {
        return $this->isServeable() && $this->canServe($userId);
    }

    public function isConfirmableBy(int $userId): bool
    {
        return $this->isConfirmable()
            && $this->shouldConfirmBy($userId)
            && $this->canConfirm($userId);
    }

    public function isCancelableBy(int $userId): bool
    {
        return $this->isCancelable() && $this->canCancel($userId);
    }

    public function shouldConfirmBy(int $userId): bool
    {
        return ($this->isSelfIssued() && $this->isOwner($userId)) || $this->isIssuer($userId);
    }

    public function isSelfIssued(): bool
    {
        if ($this->issuer === null || $this->contact === null || $this->contact->user === null) {
            return false;
        }

        return (int) $this->issuer->id === (int) $this->contact->user->id;
    }

    public function isServeable(): bool
    {
        return $this->isActive() && $this->isDue();
    }

    public function isConfirmable(): bool
    {
        return $this->status === AppointmentStatus::Reserved && $this->isFuture();
    }

    public function isCancelable(): bool
    {
        return $this->isActive();
    }

    public function doReserve(): self
    {
        if ($this->status === null) {
            $this->status = AppointmentStatus::Reserved;
        }

        return $this;
    }

    public function doConfirm(): self
    {
        if ($this->isConfirmable()) {
            $this->status = AppointmentStatus::Confirmed;
            $this->save();
        }

        return $this;
    }

    public function doCancel(): self
    {
        if ($this->isCancelable()) {
            $this->status = AppointmentStatus::Annulated;
            $this->save();
        }

        return $this;
    }

    public function doServe(): self
    {
        if ($this->isServeable()) {
            $this->status = AppointmentStatus::Served;
            $this->save();
        }

        return $this;
    }
}
