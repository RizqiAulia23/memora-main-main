<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStore
{
    public function __construct(
        private readonly string $directory,
    ) {}

    public function store(UploadedFile $file): string
    {
        return $file->store($this->directory, 'public');
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
