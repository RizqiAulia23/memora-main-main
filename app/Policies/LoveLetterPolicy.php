<?php

namespace App\Policies;

use App\Models\LoveLetter;
use App\Models\User;

class LoveLetterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LoveLetter $loveLetter): bool
    {
        return $loveLetter->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
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
