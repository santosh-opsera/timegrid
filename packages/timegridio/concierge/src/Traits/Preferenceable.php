<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;
use Timegridio\Concierge\Models\Preference;

trait Preferenceable
{
    public function preferences(): MorphMany
    {
        return $this->morphMany(Preference::class, 'preferenceable');
    }

    public function pref(string $key, mixed $value = null, string $type = 'string'): mixed
    {
        if (func_num_args() > 1) {
            $this->preferences()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $this->castPreferenceValue($value, $type),
                    'type' => $type,
                ]
            );

            Cache::put("{$this->slug}.{$key}", $value, 60);

            return $value;
        }

        if ($cached = Cache::get("{$this->slug}.{$key}")) {
            return $cached;
        }

        if ($pref = $this->preferences()->forKey($key)->first()) {
            $value = $pref->typedValue();
            $type = $pref->valueType();
        } else {
            $default = Preference::getDefault($this, $key);
            $value = $default->typedValue();
            $type = $default->valueType();
        }

        Cache::put("{$this->slug}.{$key}", $value, 60);

        return $this->castPreferenceValue($value, $type);
    }

    private function castPreferenceValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool' => (bool) $value,
            'int' => (int) $value,
            'float' => (float) $value,
            'string' => $value,
            'array' => is_array($value) ? serialize($value) : unserialize((string) $value),
            default => $value,
        };
    }
}
