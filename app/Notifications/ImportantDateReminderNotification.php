<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportantDateReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
        private readonly string $dateTitle,
        private readonly string $date,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Important date added',
            'message' => "{$this->fromName} added an important date: {$this->dateTitle} on {$this->date}.",
            'url' => route('important-dates.index'),
        ];
    }
}
