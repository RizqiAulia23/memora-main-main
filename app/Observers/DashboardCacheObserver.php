<?php

namespace App\Observers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;

class DashboardCacheObserver
{
    public function saved(object $model): void
    {
        $this->flush($model);
    }

    public function deleted(object $model): void
    {
        $this->flush($model);
    }

    private function flush(object $model): void
    {
        if (isset($model->user_id)) {
            Cache::forget(DashboardService::cacheKey($model->user_id));
        }
    }
}
