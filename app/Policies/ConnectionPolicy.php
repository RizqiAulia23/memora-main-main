<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\User;

class ConnectionPolicy
{
    public function view(User $user, Connection $connection): bool
    {
        return $user->id === $connection->sender_id || $user->id === $connection->receiver_id;
    }

    public function create(User $user, User $target): bool
    {
        return $target->id !== $user->id;
    }

    public function accept(User $user, Connection $connection): bool
    {
        return $user->id === $connection->receiver_id && $connection->status === Connection::PENDING;
    }

    public function reject(User $user, Connection $connection): bool
    {
        return $user->id === $connection->receiver_id && $connection->status === Connection::PENDING;
    }

    public function cancel(User $user, Connection $connection): bool
    {
        return $user->id === $connection->sender_id && $connection->status === Connection::PENDING;
    }

    public function delete(User $user, Connection $connection): bool
    {
        if ($connection->status === Connection::PENDING) {
            return $user->id === $connection->sender_id;
        }

        return $connection->status === Connection::ACCEPTED
            && ($user->id === $connection->sender_id || $user->id === $connection->receiver_id);
    }
}
