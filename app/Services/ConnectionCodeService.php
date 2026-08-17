<?php

namespace App\Services;

use App\Models\User;

class ConnectionCodeService
{
    /**
     * Maximum attempts before giving up on generating a unique code.
     * The 8-digit space holds 90 million codes, so collisions are unlikely.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Generate a unique 8-digit numeric connection code.
     *
     * @throws \RuntimeException when no unique code could be generated
     */
    public function generateUnique(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = (string) random_int(10000000, 99999999);

            if (! User::query()->where('connection_code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to generate a unique connection code.');
    }

    /**
     * Resolve a user by their 8-digit connection code.
     */
    public function findUserByCode(string $code): ?User
    {
        return User::query()->where('connection_code', $code)->first();
    }
}
