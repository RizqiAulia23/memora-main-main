<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = $this->dashboard->stats($user);

        $totalMemories = $stats['total_memories'];
        $totalPhotos = $stats['total_photos'];
        $totalFavorites = $stats['total_favorites'];
        $totalLoveLetters = $stats['total_letters'];
        $newThisMonth = $stats['new_this_month'];

        $recentMemories = $this->dashboard->recentMemories($user);
        $latestGallery = $this->dashboard->latestGallery($user);
        $recentLetters = $this->dashboard->recentLetters($user);
        $timeline = $this->dashboard->timeline($user);
        $activity = $this->dashboard->activity($user);
        $calendarDays = $this->dashboard->calendar($user);
        $anniversary = $this->dashboard->upcomingAnniversary($user);
        $couple = $this->dashboard->coupleOverview($user);

        return view('dashboard', compact(
            'totalMemories',
            'totalPhotos',
            'totalFavorites',
            'totalLoveLetters',
            'newThisMonth',
            'recentMemories',
            'latestGallery',
            'recentLetters',
            'timeline',
            'activity',
            'calendarDays',
            'anniversary',
            'couple',
        ));
    }
}
