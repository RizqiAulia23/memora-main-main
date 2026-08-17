<?php

namespace App\Policies;

use App\Models\SharedPlaylist;
use App\Models\User;

class SharedPlaylistPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SharedPlaylist $playlist): bool
    {
        if ($playlist->user_id === $user->id) {
            return true;
        }

        return $playlist->partner_id === $user->id
            && $user->hasAcceptedConnectionWith($playlist->user);
    }

    public function create(User $user, ?User $partner = null): bool
    {
        if (! $partner || $partner->id === $user->id) {
            return false;
        }

        return $user->hasAcceptedConnectionWith($partner);
    }

    public function update(User $user, SharedPlaylist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }

    public function delete(User $user, SharedPlaylist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }

    public function addTrack(User $user, SharedPlaylist $playlist): bool
    {
        return $this->view($user, $playlist);
    }
}
