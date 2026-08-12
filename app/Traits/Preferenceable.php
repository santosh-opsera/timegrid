<?php

namespace App\Traits;

use App\Models\Preference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Preferenceable
{
    /**
     * Preferences owned by the model.
     *
     * @return MorphMany<Preference, $this>
     */
    public function preferences(): MorphMany
    {
        return $this->morphMany(Preference::class, 'preferenceable');
    }

    /**
     * Get or set a preference value for the model.
     */
    public function pref(string $key, mixed $value = null, string $type = Preference::TYPE_STRING): mixed
    {
        if ($value !== null) {
            $value = $this->castPreferenceValue($value, $type);

            $this->preferences()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type],
            );

            return $value;
        }

        $pref = $this->preferences()->forKey($key)->first();

        if ($pref !== null) {
            return $pref->value();
        }

        return Preference::getDefault($this, $key)->value();
    }

    /**
     * Cast a preference value to its declared storage type.
     */
    private function castPreferenceValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            Preference::TYPE_BOOL => (bool) $value,
            Preference::TYPE_INT => (int) $value,
            Preference::TYPE_FLOAT => (float) $value,
            Preference::TYPE_STRING => (string) $value,
            default => $value,
        };
    }
}
