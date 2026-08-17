<?php

namespace App\Policies;

use App\Models\SharedEvent;
use App\Models\User;

class SharedEventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SharedEvent $event): bool
    {
        if ($event->user_id === $user->id) {
            return true;
        }

        return $event->partner_id === $user->id
            && $user->hasAcceptedConnectionWith($event->user);
    }

    public function create(User $user, User $partner): bool
    {
        return $partner->id !== $user->id
            && $user->hasAcceptedConnectionWith($partner);
    }

    public function update(User $user, SharedEvent $event): bool
    {
        return $event->user_id === $user->id;
    }

    public function delete(User $user, SharedEvent $event): bool
    {
        return $event->user_id === $user->id;
    }
}
