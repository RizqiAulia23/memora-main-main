<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\Memory;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SharedMemoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SharedMemory $sharedMemory): bool
    {
        return $sharedMemory->memory->user_id === $user->id
            || $sharedMemory->partner_id === $user->id;
    }

    /**
     * Only the memory owner may share. When a partner is given, the partner
     * must be connected (accepted), must not be the owner themselves, and the
     * memory must not already be shared with that partner.
     */
    public function share(User $user, Memory $memory, ?User $partner = null): bool
    {
        if ($memory->user_id !== $user->id) {
            return false;
        }

        if ($partner === null) {
            return true;
        }

        if ($partner->id === $user->id) {
            return false;
        }

        $connected = Connection::query()
            ->where(function (Builder $query) use ($user, $partner) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $partner->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->orWhere(function (Builder $query) use ($user, $partner) {
                $query->where('sender_id', $partner->id)
                    ->where('receiver_id', $user->id)
                    ->where('status', Connection::ACCEPTED);
            })
            ->exists();

        if (! $connected) {
            return false;
        }

        return ! SharedMemory::query()
            ->where('memory_id', $memory->id)
            ->where('partner_id', $partner->id)
            ->exists();
    }

    public function delete(User $user, SharedMemory $sharedMemory): bool
    {
        return $sharedMemory->memory->user_id === $user->id;
    }
}
