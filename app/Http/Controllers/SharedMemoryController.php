<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSharedMemoryRequest;
use App\Models\Memory;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SharedMemoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SharedMemory::class);

        $user = $request->user();

        $partnerId = $request->integer('partner');
        $partner = $partnerId !== 0
            ? $user->connectedPartners()->firstWhere('id', $partnerId)
            : null;

        $sharedWithMe = SharedMemory::query()
            ->where('partner_id', $user->id)
            ->whereHas('memory', fn ($memory) => $memory->whereIn('user_id', $user->connectedPartnerIds()))
            ->when($partner, fn ($query) => $query->whereHas(
                'memory',
                fn ($memory) => $memory->where('user_id', $partner->id)
            ))
            ->with(['memory.user', 'memory.sharedWith.partner'])
            ->latest()
            ->get();

        $sharedByMe = SharedMemory::query()
            ->whereHas('memory', fn ($memory) => $memory->where('user_id', $user->id))
            ->when($partner, fn ($query) => $query->where('partner_id', $partner->id))
            ->with(['partner', 'memory.sharedWith.partner'])
            ->latest()
            ->get();

        $partners = $user->connectedPartners()->sortBy('name')->values();

        return view('shared.index', compact('sharedWithMe', 'sharedByMe', 'partners', 'partner'));
    }

    public function create(Request $request, Memory $memory): View
    {
        $this->authorize('share', [SharedMemory::class, $memory]);

        $partners = $request->user()->connectedPartners()
            ->reject(fn (User $partner) => $memory->sharedWith()->where('partner_id', $partner->id)->exists())
            ->sortBy('name')
            ->values();

        return view('shared.create', compact('memory', 'partners'));
    }

    public function store(StoreSharedMemoryRequest $request, Memory $memory): RedirectResponse
    {
        $partner = User::query()->findOrFail($request->validated('partner_id'));

        $this->authorize('share', [SharedMemory::class, $memory, $partner]);

        SharedMemory::create([
            'memory_id' => $memory->id,
            'partner_id' => $partner->id,
        ]);

        return redirect()->route('memories.show', $memory)
            ->with('success', "Memory shared with {$partner->name}.");
    }

    public function destroy(Request $request, SharedMemory $sharedMemory): RedirectResponse
    {
        $this->authorize('delete', $sharedMemory);

        $memory = $sharedMemory->memory;

        $sharedMemory->delete();

        return back()->with('success', 'Memory unshared. The memory itself is untouched.');
    }
}
