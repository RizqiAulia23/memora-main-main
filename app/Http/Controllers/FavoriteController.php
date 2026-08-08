<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Memory::class);

        $memories = $request->user()->favoriteMemories()
            ->latest('favorites.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('favorites.index', compact('memories'));
    }

    public function toggle(Request $request, Memory $memory)
    {
        $this->authorize('view', $memory);

        $user = $request->user();
        $favorited = $user->favorites()->where('memory_id', $memory->id)->exists();

        if ($favorited) {
            $user->favorites()->where('memory_id', $memory->id)->delete();
        } else {
            $user->favorites()->create(['memory_id' => $memory->id]);
        }

        $this->dashboard->flush($user);

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => ! $favorited,
                'favoritesCount' => $user->favorites()->count(),
            ]);
        }

        return back()->with('success', $favorited ? 'Removed from favorites.' : 'Added to favorites.');
    }
}
