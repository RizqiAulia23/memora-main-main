<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\SharedMemory;
use App\Models\User;

class ConnectionService
{
    /**
     * Create a new connection request, or reactivate a previously rejected
     * request between the same pair (rejected -> pending, on the SAME row).
     *
     * A single UNIQUE(sender_id, receiver_id) row per pair exists, so both
     * directions are checked before a new request is allowed.
     *
     * @throws \InvalidArgumentException when the request is not allowed
     */
    public function send(User $sender, User $receiver): Connection
    {
        if ($sender->id === $receiver->id) {
            throw new \InvalidArgumentException('You cannot connect with yourself.');
        }

        $existing = Connection::query()
            ->where(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $sender->id)->where('receiver_id', $receiver->id);
            })
            ->orWhere(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $receiver->id)->where('receiver_id', $sender->id);
            })
            ->first();

        if (! $existing) {
            return Connection::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => Connection::PENDING,
            ]);
        }

        if ($existing->sender_id === $receiver->id) {
            throw new \InvalidArgumentException('This user has already sent you a connection request.');
        }

        if ($existing->status === Connection::PENDING) {
            throw new \InvalidArgumentException('A pending connection request already exists.');
        }

        if ($existing->status === Connection::ACCEPTED) {
            throw new \InvalidArgumentException('You are already connected with this user.');
        }

        $existing->update(['status' => Connection::PENDING]);

        return $existing->fresh();
    }

    public function accept(Connection $connection): void
    {
        $connection->update(['status' => Connection::ACCEPTED]);
    }

    public function reject(Connection $connection): void
    {
        $connection->update(['status' => Connection::REJECTED]);
    }

    public function cancel(Connection $connection): void
    {
        $connection->delete();
    }

    public function disconnect(Connection $connection): void
    {
        $connection->delete();

        // Disconnecting revokes shared-memory access between the pair.
        // The original memories stay untouched.
        SharedMemory::query()
            ->where(function ($query) use ($connection) {
                $query->where('partner_id', $connection->sender_id)
                    ->whereHas('memory', fn ($memory) => $memory->where('user_id', $connection->receiver_id));
            })
            ->orWhere(function ($query) use ($connection) {
                $query->where('partner_id', $connection->receiver_id)
                    ->whereHas('memory', fn ($memory) => $memory->where('user_id', $connection->sender_id));
            })
            ->delete();
    }
}
