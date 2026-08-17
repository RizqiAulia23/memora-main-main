<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Memory::class);

        $photos = Memory::query()
            ->withImage()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere(function ($sharedBranch) use ($request) {
                        $sharedBranch
                            ->whereHas('sharedWith', fn ($shared) => $shared->where('partner_id', $request->user()->id))
                            ->whereHas('user', fn ($user) => $user->whereIn('id', $request->user()->connectedPartnerIds()));
                    });
            })
            ->with(['user' => fn ($query) => $query->select('id', 'name')])
            ->latest('memory_date')
            ->paginate(12);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('gallery._grid', ['photos' => $photos])->render(),
                'hasMore' => $photos->hasMorePages(),
                'nextUrl' => $photos->nextPageUrl(),
            ]);
        }

        return view('gallery.index', compact('photos'));
    }

    public function download(Memory $memory)
    {
        $this->authorize('view', $memory);

        abort_if(! $memory->image, 404);

        $name = Str::slug($memory->title).'.'.pathinfo($memory->image, PATHINFO_EXTENSION);

        return Storage::disk('private')->download($memory->image, $name);
    }
}
