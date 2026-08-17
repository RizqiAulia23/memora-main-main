<?php

namespace App\Policies;

use App\Models\BucketListItem;
use App\Models\User;

class BucketListItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user, ?User $partner = null): bool
    {
        if (! $partner || $partner->id === $user->id) {
            return false;
        }

        return $user->hasAcceptedConnectionWith($partner);
    }

    public function toggle(User $user, BucketListItem $item): bool
    {
        if ($item->user_id === $user->id) {
            return true;
        }

        return $item->partner_id === $user->id
            && $user->hasAcceptedConnectionWith($item->user);
    }

    public function delete(User $user, BucketListItem $item): bool
    {
        return $item->user_id === $user->id;
    }
}
