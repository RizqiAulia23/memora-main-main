<?php

namespace App\Services;

use App\Models\BucketListItem;
use App\Models\ImportantDate;
use App\Models\PlaylistTrack;
use App\Models\SharedEvent;
use App\Models\SharedMemory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function stats(User $user): array
    {
        return Cache::remember(self::cacheKey($user->id), now()->addMinutes(5), function () use ($user) {
            $memories = $user->memories()
                ->selectRaw('count(*) as total')
                ->selectRaw('sum(case when image is not null then 1 else 0 end) as photos')
                ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as fresh', [now()->startOfMonth()])
                ->first();

            return [
                'total_memories' => (int) ($memories->total ?? 0),
                'total_photos' => (int) ($memories->photos ?? 0),
                'total_favorites' => $user->favorites()->count(),
                'total_letters' => $user->loveLetters()->count(),
                'new_this_month' => (int) ($memories->fresh ?? 0),
                'unread_notifications' => $user->unreadNotifications()->count(),
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

    public function timeline(User $user, int $take = 8): Collection
    {
        return $user->memories()
            ->orderBy('memory_date')
            ->limit($take)
            ->get(['id', 'title', 'memory_date']);
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

    /**
     * Relationship overview for the user's first connected partner (or null).
     */
    public function coupleOverview(User $user): ?array
    {
        $partner = $user->connectedPartners()->first();

        if (! $partner) {
            return null;
        }

        $between = function ($query) use ($user, $partner) {
            $query->where('user_id', $user->id)->where('partner_id', $partner->id)
                ->orWhere(function ($query) use ($user, $partner) {
                    $query->where('user_id', $partner->id)->where('partner_id', $user->id);
                });
        };

        $sharedMemories = SharedMemory::query()
            ->where(function ($query) use ($user, $partner) {
                $query->whereHas('memory', fn ($memory) => $memory->where('user_id', $user->id))
                    ->where('partner_id', $partner->id);
            })
            ->orWhere(function ($query) use ($user, $partner) {
                $query->whereHas('memory', fn ($memory) => $memory->where('user_id', $partner->id))
                    ->where('partner_id', $user->id);
            })
            ->count();

        $events = SharedEvent::query()->where($between)->count();

        $dates = ImportantDate::query()->where($between)->get();

        $upcoming = $dates
            ->map(fn (ImportantDate $date) => ['title' => $date->title, 'occurrence' => $date->nextOccurrence()])
            ->filter(fn ($date) => $date['occurrence'] !== null)
            ->sortBy('occurrence')
            ->first();

        $bucketItems = BucketListItem::query()->where($between)->get();

        $playlistTracks = PlaylistTrack::query()
            ->whereHas('playlist', function ($query) use ($between) {
                $query->where($between);
            })
            ->count();

        return [
            'partner' => $partner,
            'shared_memories' => $sharedMemories,
            'events' => $events,
            'upcoming_date' => $upcoming,
            'bucket_done' => $bucketItems->where('status', BucketListItem::COMPLETED)->count(),
            'bucket_total' => $bucketItems->count(),
            'playlist_tracks' => $playlistTracks,
        ];
    }
}
