<?php

namespace App\Http\Controllers;

use App\Services\CoupleTimelineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoupleTimelineController extends Controller
{
    public function __construct(
        private readonly CoupleTimelineService $timeline,
    ) {}

    public function index(Request $request): View
    {
        $feed = $this->timeline->paginate($this->timeline->feed($request->user()));

        return view('couple-timeline.index', compact('feed'));
    }
}
