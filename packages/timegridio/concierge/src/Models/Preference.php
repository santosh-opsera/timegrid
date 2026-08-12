<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Preference extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'key',
        'value',
        'type',
        'preferenceable_type',
        'preferenceable_id',
    ];

    public static function getDefault(Model $model, string $key): self
    {
        $class = $model::class;
        $value = config("preferences.{$class}.{$key}.value");
        $type = config("preferences.{$class}.{$key}.type", 'string');

        return new self([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'preferenceable_type' => $class,
            'preferenceable_id' => $model->getKey(),
        ]);
    }

    public function preferenceable(): MorphTo
    {
        return $this->morphTo();
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
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function typedValue(): mixed
    {
        return $this->attributes['value'] ?? null;
    }

    public function valueType(): string
    {
        return (string) ($this->attributes['type'] ?? 'string');
    }
}
