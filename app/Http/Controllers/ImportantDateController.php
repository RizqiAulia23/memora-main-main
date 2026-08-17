<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportantDateRequest;
use App\Http\Requests\UpdateImportantDateRequest;
use App\Models\ImportantDate;
use App\Models\User;
use App\Notifications\ImportantDateReminderNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportantDateController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifier,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ImportantDate::class);

        $user = $request->user();

        $dates = ImportantDate::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('partner_id', $user->id)
                            ->whereIn('user_id', $user->connectedPartnerIds());
                    });
            })
            ->with('user')
            ->orderBy('date')
            ->get();

        $partners = $user->connectedPartners()->sortBy('name')->values();

        return view('important-dates.index', compact('dates', 'partners'));
    }

    public function store(StoreImportantDateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $partner = null;

        if ($request->filled('partner_id')) {
            $partner = User::query()->findOrFail($request->validated('partner_id'));
            $this->authorize('create', [ImportantDate::class, $partner]);
        }

        $date = $user->importantDates()->create([
            'partner_id' => $partner?->id,
            'title' => $request->title,
            'date' => $request->date,
            'type' => $request->type,
            'description' => $request->description,
            'recurring' => $request->boolean('recurring'),
        ]);

        if ($partner) {
            $this->notifier->notify($partner, new ImportantDateReminderNotification(
                $user->name,
                $date->title,
                $date->date->format('M j, Y'),
            ));
        }

        return redirect()->route('important-dates.index')
            ->with('success', $partner ? 'Important date saved and shared with your partner.' : 'Important date saved.');
    }

    public function edit(Request $request, ImportantDate $importantDate): View
    {
        $this->authorize('update', $importantDate);

        return view('important-dates.edit', ['date' => $importantDate]);
    }

    public function update(UpdateImportantDateRequest $request, ImportantDate $importantDate): RedirectResponse
    {
        $this->authorize('update', $importantDate);

        $importantDate->update([
            'title' => $request->title,
            'date' => $request->date,
            'type' => $request->type,
            'description' => $request->description,
            'recurring' => $request->boolean('recurring'),
        ]);

        if ($importantDate->partner_id) {
            $this->notifier->notify($importantDate->partner, new ImportantDateReminderNotification(
                $request->user()->name,
                $importantDate->title,
                $importantDate->date->format('M j, Y'),
            ));
        }

        return redirect()->route('important-dates.index')
            ->with('success', 'Important date updated.');
    }

    public function destroy(Request $request, ImportantDate $importantDate): RedirectResponse
    {
        $this->authorize('delete', $importantDate);

        $importantDate->delete();

        return redirect()->route('important-dates.index')
            ->with('success', 'Important date deleted.');
    }
}
