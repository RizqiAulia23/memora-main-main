<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AccountService
{
    private readonly ImageStore $imageStore;

    public function __construct()
    {
        $this->imageStore = new ImageStore('memories');
    }

    public function delete(User $user): void
    {
        $images = $user->memories()
            ->withImage()
            ->pluck('image')
            ->all();

        $avatar = $user->avatar;

        $user->delete();

        foreach ($images as $path) {
            if (! $this->imageStore->delete($path, 'account-deletion')) {
                Log::error('Failed to delete memory image during account deletion', ['path' => $path]);
            }
        }

        if ($avatar && ! $this->imageStore->delete($avatar, 'account-deletion')) {
            Log::error('Failed to delete avatar during account deletion', ['path' => $avatar]);
        }

        Cache::forget('dashboard.stats.'.$user->id);
        Cache::forget('storage.usage.'.$user->id);
    }
}
