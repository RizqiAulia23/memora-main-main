<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MemoryImageService
{
    public function store(UploadedFile $file): string
    {
        return $file->store('memories', 'public');
    }

    public function update(?string $currentPath, ?UploadedFile $newFile): ?string
    {
        if (! $newFile) {
            return $currentPath;
        }

        $this->delete($currentPath);

        return $this->store($newFile);
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
