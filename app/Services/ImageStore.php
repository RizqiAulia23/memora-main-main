<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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

        return $this->store($newFile);
    }

    public function delete(?string $path, ?string $context = null): bool
    {
        if (! $path) {
            return true;
        }

        if (Storage::disk(self::DISK)->delete($path)) {
            return true;
        }

        if (Storage::disk(self::DISK)->delete($path)) {
            return true;
        }

        Log::warning('File deletion failed after retry', [
            'disk' => self::DISK,
            'path' => $path,
            'context' => $context,
        ]);

        return false;
    }
}
