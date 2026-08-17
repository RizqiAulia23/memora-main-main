<?php

namespace App\Observers;

use App\Models\Memory;
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
        $userIds = [];

        if ($model instanceof Memory) {
            $userIds[] = $model->user_id;

            foreach ($model->user?->connectedPartnerIds() ?? [] as $partnerId) {
                $userIds[] = $partnerId;
            }
        } elseif (isset($model->user_id)) {
            $userIds[] = $model->user_id;
        }

        foreach (array_unique(array_filter($userIds)) as $userId) {
            Cache::forget(DashboardService::cacheKey($userId));
            Cache::forget('storage.usage.'.$userId);
        }
    }
}
