<?php

namespace App\Policies;

use App\Models\ImportantDate;
use App\Models\User;

class ImportantDatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ImportantDate $date): bool
    {
        if ($date->user_id === $user->id) {
            return true;
        }

        return $date->partner_id === $user->id
            && $user->hasAcceptedConnectionWith($date->user);
    }

    public function create(User $user, ?User $partner = null): bool
    {
        if ($partner === null) {
            return true;
        }

        return $partner->id !== $user->id
            && $user->hasAcceptedConnectionWith($partner);
    }

    public function update(User $user, ImportantDate $date): bool
    {
        return $date->user_id === $user->id;
    }

    public function delete(User $user, ImportantDate $date): bool
    {
        return $date->user_id === $user->id;
    }
}
