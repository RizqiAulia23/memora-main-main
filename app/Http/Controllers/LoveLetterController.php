<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoveLetterRequest;
use App\Http\Requests\UpdateLoveLetterRequest;
use App\Models\LoveLetter;
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

        $letters = auth()->user()->loveLetters()->pinnedFirst()->get();

        return view('letters.index', compact('letters'));
    }

    public function create(): View
    {
        $this->authorize('create', LoveLetter::class);

        return view('letters.create');
    }

    public function store(StoreLoveLetterRequest $request): RedirectResponse
    {
        $this->authorize('create', LoveLetter::class);

        $letter = $request->user()->loveLetters()->create([
            'title' => $request->validated('title'),
            'content' => $this->sanitizer->sanitize($request->validated('content')),
            'mood' => $request->validated('mood'),
            'letter_date' => $request->validated('letter_date'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Your love letter has been written.');
    }

    public function show(LoveLetter $loveLetter): View
    {
        $this->authorize('view', $loveLetter);

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
