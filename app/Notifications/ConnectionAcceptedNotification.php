<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionAcceptedNotification extends Notification
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
            'title' => 'Connection accepted',
            'message' => "{$this->fromName} accepted your connection.",
            'url' => route('connections.index'),
        ];
    }
}
