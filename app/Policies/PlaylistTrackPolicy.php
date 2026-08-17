<?php

namespace App\Policies;

use App\Models\PlaylistTrack;
use App\Models\User;

class PlaylistTrackPolicy
{
    public function delete(User $user, PlaylistTrack $track): bool
    {
        return $track->added_by === $user->id
            || $track->playlist->user_id === $user->id;
    }
}
