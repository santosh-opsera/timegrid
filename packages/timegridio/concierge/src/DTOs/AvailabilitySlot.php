<?php

declare(strict_types=1);

namespace Timegridio\Concierge\DTOs;

use Carbon\Carbon;
use Timegridio\Concierge\Models\Service;

readonly class AvailabilitySlot implements \JsonSerializable
{
    public function __construct(
        public Carbon $datetime,
        public Service $service,
        public int $duration,
    ) {}

    public function finishAt(): Carbon
    {
        return $this->datetime->copy()->addMinutes($this->duration);
    }

    /**
     * @param  array{
     *     datetime?: Carbon|string,
     *     service?: Service,
     *     duration?: int,
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        $datetime = $data['datetime'] ?? now();

        return new self(
            datetime: $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime),
            service: $data['service'],
            duration: (int) ($data['duration'] ?? 0),
        );
    }

    /**
     * @return array{
     *     datetime: string,
     *     serviceId: int,
     *     serviceSlug: string,
     *     duration: int,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'datetime' => $this->datetime->toIso8601String(),
            'serviceId' => $this->service->id,
            'serviceSlug' => $this->service->slug,
            'duration' => $this->duration,
        ];
    }
}
