<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New connection request',
            'message' => "{$this->fromName} wants to connect with you.",
            'url' => route('connections.index'),
        ];
    }
}
