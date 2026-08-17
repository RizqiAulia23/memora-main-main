<?php

if (! function_exists('assetv')) {
    function assetv(string $path): string
    {
        $file = public_path($path);
        $url = asset($path);

        if (! is_file($file)) {
            return $url;
        }

        return $url.'?v='.filemtime($file);
    }
}

if (! function_exists('notification_icon')) {
    function notification_icon(string $type): string
    {
        return match (true) {
            str_contains($type, 'ConnectionRequest') => 'fa-user-plus',
            str_contains($type, 'ConnectionAccepted') => 'fa-user-check',
            str_contains($type, 'LoveLetter') => 'fa-envelope-open-text',
            str_contains($type, 'MemoryShared') => 'fa-share-nodes',
            str_contains($type, 'ImportantDate') => 'fa-gift',
            str_contains($type, 'SharedEvent') => 'fa-calendar-alt',
            str_contains($type, 'BucketList') => 'fa-list-check',
            default => 'fa-bell',
        };
    }
}
