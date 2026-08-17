<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoveLetterReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
        private readonly string $letterTitle,
        private readonly int $letterId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New love letter',
            'message' => "{$this->fromName} wrote you a love letter: {$this->letterTitle}",
            'url' => route('letters.show', $this->letterId),
        ];
    }
}
