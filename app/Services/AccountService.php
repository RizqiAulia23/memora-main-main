<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function delete(User $user): void
    {
        $user->memories()
            ->withImage()
            ->pluck('image')
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Cache::forget('dashboard.stats.'.$user->id);

        $user->delete();
    }
}
