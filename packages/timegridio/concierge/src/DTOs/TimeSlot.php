<?php

declare(strict_types=1);

namespace Timegridio\Concierge\DTOs;

use Carbon\Carbon;

readonly class TimeSlot implements \JsonSerializable
{
    public function __construct(
        public Carbon $startAt,
        public Carbon $finishAt,
    ) {}

    public function durationInMinutes(): int
    {
        return (int) $this->startAt->diffInMinutes($this->finishAt);
    }

    public function contains(Carbon $instant): bool
    {
        return $instant->greaterThanOrEqualTo($this->startAt)
            && $instant->lessThan($this->finishAt);
    }

    public function overlaps(self $other): bool
    {
        return $this->startAt->lessThan($other->finishAt)
            && $this->finishAt->greaterThan($other->startAt);
    }

    /**
     * @param  array{startAt: Carbon|string, finishAt: Carbon|string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            startAt: $data['startAt'] instanceof Carbon
                ? $data['startAt']
                : Carbon::parse($data['startAt']),
            finishAt: $data['finishAt'] instanceof Carbon
                ? $data['finishAt']
                : Carbon::parse($data['finishAt']),
        );
    }

    /**
     * @return array{startAt: string, finishAt: string, duration: int}
     */
    public function toArray(): array
    {
        return [
            'startAt' => $this->startAt->toIso8601String(),
            'finishAt' => $this->finishAt->toIso8601String(),
            'duration' => $this->durationInMinutes(),
        ];
    }

    /**
     * @return array{startAt: string, finishAt: string, duration: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
