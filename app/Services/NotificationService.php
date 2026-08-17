<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationService
{
    public function notify(User $user, Notification $notification): void
    {
        $user->notify($notification);

        // The unread count shown in the bell is cached inside the dashboard
        // stats; keep it fresh whenever a new notification lands.
        app(DashboardService::class)->flush($user);
    }
}
