<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStore
{
    private const DISK = 'private';

    public function __construct(
        private readonly string $directory,
    ) {}

    public function store(UploadedFile $file): string
    {
        return $file->store($this->directory, self::DISK);
    }

    public function update(?string $currentPath, ?UploadedFile $newFile): ?string
    {
        if (! $newFile) {
            return $currentPath;
        }

        $newPath = $this->store($newFile);

        $this->delete($currentPath);

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
