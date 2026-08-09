<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function stats(User $user): array
    {
        return Cache::remember(self::cacheKey($user->id), now()->addMinutes(5), function () use ($user) {
            return [
                'total_memories' => $user->memories()->count(),
                'total_photos' => $user->memories()->whereNotNull('image')->count(),
                'total_favorites' => $user->favorites()->count(),
                'total_letters' => $user->loveLetters()->count(),
                'new_this_month' => $user->memories()
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
            ];
        });
    }

    public function flush(User $user): void
    {
        Cache::forget(self::cacheKey($user->id));
    }

    public static function cacheKey(int $userId): string
    {
        return 'dashboard.stats.'.$userId;
    }

    public function recentMemories(User $user, int $take = 5): Collection
    {
        return $user->memories()->latest()->take($take)->get();
    }

    public function latestGallery(User $user, int $take = 6): Collection
    {
        return $user->memories()->withImage()->latest()->take($take)->get();
    }

    public function recentLetters(User $user, int $take = 3): Collection
    {
        return $user->loveLetters()->pinnedFirst()->take($take)->get();
    }

    public function timeline(User $user): Collection
    {
        return $user->memories()->orderBy('memory_date')->get();
    }

    public function activity(User $user, int $take = 8): Collection
    {
        return $user->memories()->latest('updated_at')->take($take)->get();
    }

    public function upcomingAnniversary(User $user): ?array
    {
        $base = $user->relationship_date ?: $user->memories()->min('memory_date');

        if (! $base) {
            return null;
        }

        $base = Carbon::parse($base);
        $next = $base->copy()->year(now()->year);

        if ($next->isPast()) {
            $next->addYear();
        }

        $daysUntil = (int) now()->startOfDay()->diffInDays($next->startOfDay());

        return [
            'date' => $next,
            'days' => $daysUntil,
            'label' => $next->format('M j, Y'),
        ];
    }

    public function calendar(User $user, ?Carbon $month = null): array
    {
        $month = $month ?: now()->startOfMonth();

        $memoryDates = $user->memories()
            ->whereYear('memory_date', $month->year)
            ->whereMonth('memory_date', $month->month)
            ->pluck('memory_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->flip();

        $days = [];

        foreach (range(1, $month->daysInMonth) as $day) {
            $date = $month->copy()->day($day);
            $days[] = $this->cell($date, $memoryDates);
        }

        for ($i = $month->dayOfWeek - 1; $i >= 0; $i--) {
            array_unshift($days, $this->cell($month->copy()->subDays($i + 1), $memoryDates, otherMonth: true));
        }

        $remaining = 7 - (count($days) % 7);
        if ($remaining < 7) {
            $last = $month->copy()->endOfMonth();
            for ($i = 1; $i <= $remaining; $i++) {
                $days[] = $this->cell($last->copy()->addDays($i), $memoryDates, otherMonth: true);
            }
        }

        return $days;
    }

    public function memoriesOnDate(User $user, Carbon $date): Collection
    {
        return $user->memories()
            ->whereDate('memory_date', $date->toDateString())
            ->orderByDesc('memory_date')
            ->get();
    }

    private function cell(Carbon $date, $memoryDates, bool $otherMonth = false): array
    {
        return [
            'day' => $date->day,
            'date' => $date->format('Y-m-d'),
            'isToday' => $date->isToday(),
            'hasMemory' => $memoryDates->has($date->format('Y-m-d')),
            'otherMonth' => $otherMonth,
        ];
    }
}
