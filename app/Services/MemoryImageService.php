<?php

namespace App\Services;

class MemoryImageService extends ImageStore
{
    public function __construct()
    {
        parent::__construct('memories');
    }
}
