<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SharedEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
        private readonly string $eventTitle,
        private readonly string $eventDate,
        private readonly int $eventId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New shared event',
            'message' => "{$this->fromName} added a shared event: {$this->eventTitle} on {$this->eventDate}.",
            'url' => route('events.show', $this->eventId),
        ];
    }
}
