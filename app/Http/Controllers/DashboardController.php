<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalMemories = $user->memories()->count();
        $totalPhotos = $user->memories()->whereNotNull('image')->count();
        $newThisMonth = $user->memories()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Prepared for when these tables exist.
        $totalFavorites = $this->countIfTableExists('favorites', $user);
        $totalLoveLetters = $this->countIfTableExists('love_letters', $user);

        $recentMemories = $user->memories()->latest('created_at')->take(5)->get();
        $timeline = $user->memories()->orderBy('memory_date')->get();
        $activity = $user->memories()->latest('updated_at')->take(6)->get();
        $calendarDays = $this->buildCalendar($user);

        return view('dashboard', compact(
            'totalMemories',
            'totalPhotos',
            'totalFavorites',
            'totalLoveLetters',
            'newThisMonth',
            'recentMemories',
            'timeline',
            'activity',
            'calendarDays',
        ));
    }

    private function countIfTableExists(string $table, User $user): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->where('user_id', $user->id)->count();
    }

    private function buildCalendar(User $user): array
    {
        $memoryDates = $user->memories()
            ->pluck('memory_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->flip();

        $first = now()->startOfMonth();
        $today = now()->format('Y-m-d');
        $days = [];

        foreach (range(1, $first->daysInMonth) as $day) {
            $date = $first->copy()->day($day);
            $days[] = $this->calendarDay($date, $memoryDates, $today);
        }

        for ($i = $first->dayOfWeek - 1; $i >= 0; $i--) {
            array_unshift($days, $this->calendarDay($first->copy()->subDays($i + 1), $memoryDates, $today, otherMonth: true));
        }

        $remaining = 7 - (count($days) % 7);
        if ($remaining < 7) {
            $last = $first->copy()->endOfMonth();
            for ($i = 1; $i <= $remaining; $i++) {
                $days[] = $this->calendarDay($last->copy()->addDays($i), $memoryDates, $today, otherMonth: true);
            }
        }

        return $days;
    }

    private function calendarDay(Carbon $date, $memoryDates, string $today, bool $otherMonth = false): array
    {
        return [
            'day' => $date->day,
            'date' => $date->format('Y-m-d'),
            'isToday' => $date->format('Y-m-d') === $today,
            'hasMemory' => $memoryDates->has($date->format('Y-m-d')),
            'otherMonth' => $otherMonth,
        ];
    }
}
