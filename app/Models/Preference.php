<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $type
 * @property string $preferenceable_type
 * @property int $preferenceable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Model $preferenceable
 */
class Preference extends Model
{
    public const TYPE_STRING = 'string';

    public const TYPE_BOOL = 'bool';

    public const TYPE_INT = 'int';

    public const TYPE_FLOAT = 'float';

    public const TYPE_ARRAY = 'array';

    public const TYPE_TIME = 'time';

    public const TYPE_JSON = 'json';

    /**
     * Supported preference value types.
     *
     * @var list<string>
     */
    public const TYPES = [
        self::TYPE_STRING,
        self::TYPE_BOOL,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_ARRAY,
        self::TYPE_TIME,
        self::TYPE_JSON,
    ];

    /**
     * Supported polymorphic owner types.
     *
     * @var list<class-string<Model>>
     */
    public const PREFERENCEABLE_TYPES = [
        User::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'preferenceable_type',
        'preferenceable_id',
    ];

    /**
     * Owning model for this preference.
     *
     * @return MorphTo<Model, $this>
     */
    public function preferenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function __toString(): string
    {
        return (string) $this->attributes['value'];
    }

    /**
     * Build a preference instance using configured defaults.
     */
    public static function getDefault(Model $model, string $key): self
    {
        $class = $model::class;
        $value = config("preferences.{$class}.{$key}.value");
        $type = config("preferences.{$class}.{$key}.type", self::TYPE_STRING);

        return new self([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'preferenceable_type' => $class,
            'preferenceable_id' => $model->getKey(),
        ]);
    }

    public function question(): string
    {
        return (string) trans("preferences.{$this->preferenceable_type}.question.{$this->key}");
    }

    public function help(): string
    {
        return (string) trans("preferences.{$this->preferenceable_type}.help.{$this->key}");
    }

    /**
     * Scope preferences to a specific key.
     *
     * @param  Builder<Preference>  $query
     * @return Builder<Preference>
     */
    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * Return the stored value cast to its declared type.
     */
    public function value(): mixed
    {
        return match ($this->type) {
            self::TYPE_STRING => (string) $this->value,
            self::TYPE_BOOL => (bool) $this->value,
            self::TYPE_INT => (int) $this->value,
            self::TYPE_FLOAT => (float) $this->value,
            default => $this->value,
        };
    }
}
