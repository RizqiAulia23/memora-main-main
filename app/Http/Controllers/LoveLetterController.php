<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoveLetterRequest;
use App\Http\Requests\UpdateLoveLetterRequest;
use App\Models\LoveLetter;
use App\Models\User;
use App\Services\RichTextSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoveLetterController extends Controller
{
    public function __construct(
        private readonly RichTextSanitizer $sanitizer,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', LoveLetter::class);

        $user = auth()->user();

        $mine = $user->loveLetters()
            ->with('receiver')
            ->pinnedFirst()
            ->paginate(10)
            ->withQueryString();

        $received = LoveLetter::query()
            ->where('receiver_id', $user->id)
            ->with('user')
            ->unreadFirst()
            ->get();

        return view('letters.index', compact('mine', 'received'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LoveLetter::class);

        $partners = $request->user()->connectedPartners()->sortBy('name')->values();

        $selectedReceiverId = $request->integer('receiver_id');
        if ($selectedReceiverId !== 0 && ! $partners->contains('id', $selectedReceiverId)) {
            $selectedReceiverId = 0;
        }

        return view('letters.create', compact('partners', 'selectedReceiverId'));
    }

    public function store(StoreLoveLetterRequest $request): RedirectResponse
    {
        $receiver = $request->validated('receiver_id')
            ? User::query()->findOrFail($request->validated('receiver_id'))
            : null;

        if ($receiver !== null) {
            $this->authorize('create', [LoveLetter::class, $receiver]);
        }

        $letter = $request->user()->loveLetters()->create([
            'title' => $request->validated('title'),
            'content' => $this->sanitizer->sanitize($request->validated('content')),
            'mood' => $request->validated('mood'),
            'letter_date' => $request->validated('letter_date'),
            'is_pinned' => $request->boolean('is_pinned'),
            'receiver_id' => $receiver?->id,
        ]);

        return redirect()->route('letters.show', $letter)->with(
            'success',
            $receiver ? "Your love letter has been sent to {$receiver->name}." : 'Your love letter has been written.'
        );
    }

    public function show(Request $request, LoveLetter $loveLetter): View
    {
        $this->authorize('view', $loveLetter);

        $loveLetter->load(['user', 'receiver']);

        if ($loveLetter->receiver_id === $request->user()->id && $loveLetter->read_at === null) {
            $loveLetter->update(['read_at' => now()]);
            $loveLetter->refresh();
        }

        return view('letters.show', compact('loveLetter'));
    }

    public function edit(LoveLetter $loveLetter): View
    {
        $this->authorize('update', $loveLetter);

        return view('letters.edit', compact('loveLetter'));
    }

    public function update(UpdateLoveLetterRequest $request, LoveLetter $loveLetter): RedirectResponse
    {
        $this->authorize('update', $loveLetter);

        $loveLetter->update([
            'title' => $request->validated('title'),
            'content' => $this->sanitizer->sanitize($request->validated('content')),
            'mood' => $request->validated('mood'),
            'letter_date' => $request->validated('letter_date'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('letters.show', $loveLetter)->with('success', 'Love letter updated.');
    }

    public function destroy(LoveLetter $loveLetter): RedirectResponse
    {
        $this->authorize('delete', $loveLetter);

        $loveLetter->delete();

        return redirect()->route('letters.index')->with('success', 'Love letter deleted.');
    }

    public function togglePin(Request $request, LoveLetter $loveLetter): RedirectResponse
    {
        $this->authorize('togglePin', $loveLetter);

        $loveLetter->update(['is_pinned' => ! $loveLetter->is_pinned]);

        return back()->with('success', $loveLetter->is_pinned ? 'Letter pinned.' : 'Letter unpinned.');
    }
}
