<?php

namespace App\Policies;

use App\Models\Memory;
use App\Models\SharedMemory;
use App\Models\User;

class MemoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Memory $memory): bool
    {
        if ($memory->user_id === $user->id) {
            return true;
        }

        return SharedMemory::query()
            ->where('memory_id', $memory->id)
            ->where('partner_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Memory $memory): bool
    {
        return $memory->user_id === $user->id;
    }

    public function delete(User $user, Memory $memory): bool
    {
        return $memory->user_id === $user->id;
    }
}
