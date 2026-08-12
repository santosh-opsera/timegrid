<?php

declare(strict_types=1);

namespace App\Observers;

use App\Support\ImpersonationContext;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    /** @var list<string> */
    private const SENSITIVE_ATTRIBUTES = [
        'password',
        'remember_token',
        'firstname',
        'lastname',
        'birthdate',
        'nin',
        'mobile',
        'phone',
        'national_id',
        'ssn',
        'email',
        'name',
    ];

    public function created(Model $model): void
    {
        $this->record('created', $model, [
            'attributes' => $this->sanitizeAttributes($model->getAttributes()),
        ]);
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();

        if ($changes === []) {
            return;
        }

        $original = [];

        foreach (array_keys($changes) as $key) {
            $original[$key] = $model->getOriginal($key);
        }

        $this->record('updated', $model, [
            'old' => $this->sanitizeAttributes($original),
            'attributes' => $this->sanitizeAttributes($changes),
        ]);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, [
            'attributes' => $this->sanitizeAttributes($model->getAttributes()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(string $event, Model $model, array $properties): void
    {
        $causer = Auth::user();

        ActivityLog::query()->create([
            'log_name' => class_basename($model),
            'description' => "{$event}",
            'subject_type' => $model->getMorphClass(),
            'subject_id' => $model->getKey(),
            'causer_type' => $causer !== null ? $causer->getMorphClass() : null,
            'causer_id' => $causer?->getKey(),
            'impersonator_id' => ImpersonationContext::id(),
            'properties' => $properties,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function sanitizeAttributes(array $attributes): array
    {
        $sanitized = [];

        foreach ($attributes as $key => $value) {
            if ($this->isSensitiveAttribute((string) $key)) {
                $sanitized[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeAttributes($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveAttribute(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_ATTRIBUTES, true);
    }
}
