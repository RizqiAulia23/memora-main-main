<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoryRequest;
use App\Http\Requests\UpdateMemoryRequest;
use App\Models\Memory;
use App\Services\MemoryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemoryController extends Controller
{
    public function __construct(
        private readonly MemoryImageService $imageService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Memory::class);

        $favoritesFilter = $request->boolean('favorites');

        $memories = $request->user()->memories()
            ->search($request->query('search'))
            ->sort($request->query('sort'))
            ->when($favoritesFilter, fn ($query) => $query->favorited())
            ->with([
                'favorites' => fn ($query) => $query->where('user_id', $request->user()->id),
            ])
            ->withCount('sharedWith')
            ->paginate(10)
            ->withQueryString();

        // Only fetch shared-with partners when a card actually shows them,
        // keeping the query count flat when nothing is shared.
        if ($memories->contains(fn ($memory) => $memory->shared_with_count > 0)) {
            $memories->load('sharedWith.partner');
        }

        return view('memories.index', compact('memories', 'favoritesFilter'));
    }

    public function create()
    {
        $this->authorize('create', Memory::class);

        return view('memories.create');
    }

    public function store(StoreMemoryRequest $request)
    {
        $this->authorize('create', Memory::class);

        $image = $request->hasFile('image')
            ? $this->imageService->store($request->file('image'))
            : null;

        try {
            $memory = $request->user()->memories()->create([
                'title' => $request->title,
                'description' => $request->description,
                'memory_date' => $request->memory_date,
                'image' => $image,
            ]);
        } catch (\Throwable $exception) {
            if ($image) {
                $this->imageService->delete($image, 'memory-store-cleanup');
            }

            throw $exception;
        }

        return redirect()->route('memories.show', $memory)
            ->with('success', 'Memory created successfully.');
    }

    public function show(Memory $memory)
    {
        $this->authorize('view', $memory);

        $memory->load(['user', 'sharedWith.partner']);

        return view('memories.show', compact('memory'));
    }

    public function edit(Memory $memory)
    {
        $this->authorize('update', $memory);

        return view('memories.edit', compact('memory'));
    }

    public function update(UpdateMemoryRequest $request, Memory $memory)
    {
        $this->authorize('update', $memory);

        $oldImage = $memory->image;
        $newImage = $this->imageService->update($oldImage, $request->file('image'));

        try {
            $memory->update([
                'title' => $request->title,
                'description' => $request->description,
                'memory_date' => $request->memory_date,
                'image' => $newImage,
            ]);
        } catch (\Throwable $exception) {
            if ($newImage !== $oldImage) {
                $this->imageService->delete($newImage, 'memory-update-cleanup');
            }

            throw $exception;
        }

        if ($newImage !== $oldImage) {
            $this->imageService->delete($oldImage, 'memory-image-replacement');
        }

        return redirect()->route('memories.show', $memory)
            ->with('success', 'Memory updated successfully.');
    }

    public function destroy(Memory $memory)
    {
        $this->authorize('delete', $memory);

        $image = $memory->image;

        $memory->delete();

        $this->imageService->delete($image, 'memory-destroy');

        return redirect()->route('memories.index')
            ->with('success', 'Memory deleted successfully.');
    }

    public function image(Memory $memory)
    {
        $this->authorize('view', $memory);

        abort_if(! $memory->image, 404);

        return Storage::disk('private')->response($memory->image);
    }
}
