<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BucketListUpdateNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fromName,
        private readonly string $action,
        private readonly string $itemTitle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Bucket list update',
            'message' => "{$this->fromName} {$this->action} '{$this->itemTitle}'.",
            'url' => route('bucket-list.index'),
        ];
    }
}
