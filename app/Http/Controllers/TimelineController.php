<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Memory::class);

        $user = $request->user();

        $memories = $user->memories()
            ->orderBy('memory_date')
            ->get(['id', 'title', 'image', 'memory_date']);

        $years = $memories
            ->groupBy(fn ($memory) => $memory->memory_date->year)
            ->keys()
            ->sortDesc()
            ->values();

        $selectedYear = $request->integer('year') ?: ($years->first() ?? now()->year);

        $grouped = $memories
            ->filter(fn ($memory) => $memory->memory_date->year === $selectedYear)
            ->groupBy(fn ($memory) => $memory->memory_date->format('F'));

        return view('timeline.index', compact('years', 'selectedYear', 'grouped'));
    }
}
