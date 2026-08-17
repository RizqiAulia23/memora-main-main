<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSharedEventRequest;
use App\Http\Requests\UpdateSharedEventRequest;
use App\Models\SharedEvent;
use App\Models\User;
use App\Notifications\SharedEventNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SharedEventController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifier,
    ) {}

    public function create(Request $request): View
    {
        $partners = $request->user()->connectedPartners()->sortBy('name')->values();

        return view('calendar.events.create', compact('partners'));
    }

    public function store(StoreSharedEventRequest $request): RedirectResponse
    {
        $partner = User::query()->findOrFail($request->validated('partner_id'));

        $this->authorize('create', [SharedEvent::class, $partner]);

        $event = $request->user()->events()->create([
            'partner_id' => $partner->id,
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'location' => $request->location,
            'color' => $request->color,
        ]);

        $this->notifier->notify($partner, new SharedEventNotification(
            $request->user()->name,
            $event->title,
            $event->event_date->format('M j, Y'),
            $event->id,
        ));

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created and shared with your partner.');
    }

    public function show(Request $request, SharedEvent $sharedEvent): View
    {
        $this->authorize('view', $sharedEvent);

        $sharedEvent->load(['user', 'partner']);

        return view('calendar.events.show', ['event' => $sharedEvent]);
    }

    public function edit(Request $request, SharedEvent $sharedEvent): View
    {
        $this->authorize('update', $sharedEvent);

        return view('calendar.events.edit', ['event' => $sharedEvent]);
    }

    public function update(UpdateSharedEventRequest $request, SharedEvent $sharedEvent): RedirectResponse
    {
        $this->authorize('update', $sharedEvent);

        $sharedEvent->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'location' => $request->location,
            'color' => $request->color,
        ]);

        $this->notifier->notify($sharedEvent->partner, new SharedEventNotification(
            $request->user()->name,
            $sharedEvent->title,
            $sharedEvent->event_date->format('M j, Y'),
            $sharedEvent->id,
        ));

        return redirect()->route('events.show', $sharedEvent)
            ->with('success', 'Event updated.');
    }

    public function destroy(Request $request, SharedEvent $sharedEvent): RedirectResponse
    {
        $this->authorize('delete', $sharedEvent);

        $sharedEvent->delete();

        return redirect()->route('calendar.index')
            ->with('success', 'Event deleted.');
    }
}
