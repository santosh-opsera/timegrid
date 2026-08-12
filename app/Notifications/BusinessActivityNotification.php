<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BusinessActivityNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $category,
        public User $from,
        public array $extra = [],
        public ?string $url = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category,
            'from'     => [
                'id'   => $this->from->id,
                'name' => $this->from->name,
            ],
            'extra' => $this->extra,
            'url'   => $this->url,
        ];
    }
}
