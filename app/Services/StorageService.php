<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    public function usageForUser(User $user): int
    {
        return $user->memories()
            ->withImage()
            ->pluck('image')
            ->sum(fn (string $path) => (int) Storage::disk('public')->size($path));
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = (int) floor(log($bytes, 1024));

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
