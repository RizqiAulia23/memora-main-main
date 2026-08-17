<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemorySharedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
        private readonly string $memoryTitle,
        private readonly int $memoryId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Memory shared with you',
            'message' => "{$this->fromName} shared '{$this->memoryTitle}' with you.",
            'url' => route('memories.show', $this->memoryId),
        ];
    }
}
