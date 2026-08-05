<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoryRequest;
use App\Http\Requests\UpdateMemoryRequest;
use App\Models\Memory;
use App\Services\MemoryImageService;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    public function __construct(
        private readonly MemoryImageService $imageService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Memory::class);

        $memories = $request->user()->memories()
            ->search($request->query('search'))
            ->sort($request->query('sort'))
            ->paginate(10)
            ->withQueryString();

        return view('memories.index', compact('memories'));
    }

    public function create()
    {
        $this->authorize('create', Memory::class);

        return view('memories.create');
    }

    public function store(StoreMemoryRequest $request)
    {
        $this->authorize('create', Memory::class);

        $memory = $request->user()->memories()->create([
            'title' => $request->title,
            'description' => $request->description,
            'memory_date' => $request->memory_date,
            'image' => $request->hasFile('image')
                ? $this->imageService->store($request->file('image'))
                : null,
        ]);

        return redirect()->route('memories.show', $memory)
            ->with('success', 'Memory created successfully.');
    }

    public function show(Memory $memory)
    {
        $this->authorize('view', $memory);

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

        $memory->update([
            'title' => $request->title,
            'description' => $request->description,
            'memory_date' => $request->memory_date,
            'image' => $this->imageService->update($memory->image, $request->file('image')),
        ]);

        return redirect()->route('memories.show', $memory)
            ->with('success', 'Memory updated successfully.');
    }

    public function destroy(Memory $memory)
    {
        $this->authorize('delete', $memory);

        $this->imageService->delete($memory->image);
        $memory->delete();

        return redirect()->route('memories.index')
            ->with('success', 'Memory deleted successfully.');
    }
}
