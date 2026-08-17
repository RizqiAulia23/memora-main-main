<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Models\SharedEvent;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Memory::class);
        $this->authorize('viewAny', SharedEvent::class);

        $month = now()->startOfMonth();

        if ($request->query('month')) {
            try {
                $parsed = Carbon::createFromFormat('Y-m', (string) $request->query('month'))->startOfMonth();

                if ($parsed instanceof Carbon) {
                    $month = $parsed;
                }
            } catch (\Throwable) {
                // Invalid month falls back to the current month.
            }
        }

        $days = $this->dashboard->calendar($request->user(), $month);

        $events = $this->eventsInMonth($request->user(), $month);

        return view('calendar.index', [
            'days' => $days,
            'events' => $events,
            'eventDates' => $events
                ->map(fn ($event) => $event->event_date->format('Y-m-d'))
                ->unique()
                ->flip(),
            'month' => $month,
            'yearMonth' => $month->format('Y-m'),
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'hasMemories' => $request->user()->memories()->exists(),
        ]);
    }

    public function onDate(Request $request)
    {
        $this->authorize('viewAny', Memory::class);
        $this->authorize('viewAny', SharedEvent::class);

        if (! $request->query('date')) {
            return response()->json(['message' => 'The date is required.'], 422);
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $request->query('date'))->startOfDay();
        } catch (\Throwable) {
            return response()->json(['message' => 'The date format is invalid.'], 422);
        }

        $memories = $this->dashboard->memoriesOnDate($request->user(), $date);

        $events = SharedEvent::query()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere(function ($query) use ($request) {
                        $query->where('partner_id', $request->user()->id)
                            ->whereIn('user_id', $request->user()->connectedPartnerIds());
                    });
            })
            ->whereDate('event_date', $date->toDateString())
            ->with('user')
            ->orderBy('event_time')
            ->get();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'html' => ViewFactory::make('calendar._date-memories', [
                'memories' => $memories,
                'events' => $events,
                'date' => $date,
            ])->render(),
        ]);
    }

    private function eventsInMonth(User $user, Carbon $month)
    {
        return SharedEvent::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('partner_id', $user->id)
                            ->whereIn('user_id', $user->connectedPartnerIds());
                    });
            })
            ->whereYear('event_date', $month->year)
            ->whereMonth('event_date', $month->month)
            ->with('user')
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();
    }
}
