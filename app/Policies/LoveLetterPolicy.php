<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\LoveLetter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LoveLetterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LoveLetter $loveLetter): bool
    {
        return $loveLetter->user_id === $user->id || $loveLetter->receiver_id === $user->id;
    }

    /**
     * A personal letter (no receiver) is always allowed. A letter sent to a
     * partner requires an accepted connection in either direction, and the
     * receiver must not be the sender themselves.
     */
    public function create(User $user, ?User $receiver = null): bool
    {
        if ($receiver === null) {
            return true;
        }

        if ($receiver->id === $user->id) {
            return false;
        }

        return Connection::query()
            ->where(function (Builder $query) use ($user, $receiver) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $receiver->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->orWhere(function (Builder $query) use ($user, $receiver) {
                $query->where('sender_id', $receiver->id)
                    ->where('receiver_id', $user->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->exists();
    }

    public function update(User $user, LoveLetter $loveLetter): bool
    {
        return $loveLetter->user_id === $user->id;
    }

    public function delete(User $user, LoveLetter $loveLetter): bool
    {
        return $loveLetter->user_id === $user->id;
    }

    public function togglePin(User $user, LoveLetter $loveLetter): bool
    {
        return $loveLetter->user_id === $user->id;
    }
}
