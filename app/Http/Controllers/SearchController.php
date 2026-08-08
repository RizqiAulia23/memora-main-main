<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Memory::class);

        $query = trim((string) $request->query('q'));
        $results = $query ? $this->search($request->user(), $query) : [];

        return view('search.index', compact('query', 'results'));
    }

    public function instant(Request $request)
    {
        $this->authorize('viewAny', Memory::class);

        $query = trim((string) $request->query('q'));
        $results = $query ? $this->search($request->user(), $query) : [];

        return response()->json([
            'query' => $query,
            'html' => ViewFactory::make('search._results', ['query' => $query, 'results' => $results])->render(),
        ]);
    }

    private function search($user, string $query): array
    {
        $memories = $user->memories()->search($query)->latest()->take(8)->get();
        $photos = $user->memories()->withImage()->search($query)->latest()->take(8)->get();
        $letters = $user->loveLetters()->search($query)->pinnedFirst()->take(8)->get();

        return compact('memories', 'photos', 'letters');
    }
}
