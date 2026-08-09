<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    private const DISK = 'private';

    public function delete(User $user): void
    {
        $user->memories()
            ->withImage()
            ->pluck('image')
            ->each(fn (string $path) => Storage::disk(self::DISK)->delete($path));

        if ($user->avatar) {
            Storage::disk(self::DISK)->delete($user->avatar);
        }

        Cache::forget('dashboard.stats.'.$user->id);
        Cache::forget('storage.usage.'.$user->id);

        $user->delete();
    }
}
