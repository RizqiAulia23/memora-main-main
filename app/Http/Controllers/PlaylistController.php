<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaylistRequest;
use App\Http\Requests\StorePlaylistTrackRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Models\PlaylistTrack;
use App\Models\SharedPlaylist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SharedPlaylist::class);

        $user = $request->user();

        $playlists = SharedPlaylist::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('partner_id', $user->id)
                            ->whereIn('user_id', $user->connectedPartnerIds());
                    });
            })
            ->with(['tracks.adder', 'user', 'partner'])
            ->orderByDesc('updated_at')
            ->get();

        $partners = $user->connectedPartners()->sortBy('name')->values();

        return view('playlists.index', compact('playlists', 'partners'));
    }

    public function store(StorePlaylistRequest $request): RedirectResponse
    {
        $user = $request->user();
        $partner = User::query()->findOrFail($request->validated('partner_id'));

        $this->authorize('create', [SharedPlaylist::class, $partner]);

        $duplicate = SharedPlaylist::query()
            ->where(function ($query) use ($user, $partner) {
                $query->where('user_id', $user->id)->where('partner_id', $partner->id)
                    ->orWhere(function ($query) use ($user, $partner) {
                        $query->where('user_id', $partner->id)->where('partner_id', $user->id);
                    });
            })
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['partner_id' => 'You already have a playlist with this partner.']);
        }

        $user->playlists()->create([
            'partner_id' => $partner->id,
            'name' => $request->name,
        ]);

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist created and shared with your partner.');
    }

    public function edit(Request $request, SharedPlaylist $sharedPlaylist): View
    {
        $this->authorize('update', $sharedPlaylist);

        return view('playlists.edit', ['playlist' => $sharedPlaylist]);
    }

    public function update(UpdatePlaylistRequest $request, SharedPlaylist $sharedPlaylist): RedirectResponse
    {
        $this->authorize('update', $sharedPlaylist);

        $sharedPlaylist->update(['name' => $request->name]);

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist renamed.');
    }

    public function destroy(Request $request, SharedPlaylist $sharedPlaylist): RedirectResponse
    {
        $this->authorize('delete', $sharedPlaylist);

        $sharedPlaylist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist deleted.');
    }

    public function addTrack(StorePlaylistTrackRequest $request, SharedPlaylist $sharedPlaylist): RedirectResponse
    {
        $this->authorize('addTrack', $sharedPlaylist);

        $duplicate = $sharedPlaylist->tracks()
            ->get(['title', 'artist'])
            ->contains(fn (PlaylistTrack $track) => mb_strtolower($track->title) === mb_strtolower($request->title)
                && mb_strtolower($track->artist) === mb_strtolower($request->artist));

        if ($duplicate) {
            return back()->with('error', 'This track is already in the playlist.');
        }

        $sharedPlaylist->tracks()->create([
            'added_by' => $request->user()->id,
            'title' => $request->title,
            'artist' => $request->artist,
            'url' => $request->url,
            'thumbnail' => $request->thumbnail,
            'position' => $sharedPlaylist->tracks()->count(),
        ]);

        return redirect()->route('playlists.index')
            ->with('success', 'Track added to the playlist.');
    }

    public function removeTrack(Request $request, SharedPlaylist $sharedPlaylist, PlaylistTrack $playlistTrack): RedirectResponse
    {
        $this->authorize('delete', $playlistTrack);

        $playlistTrack->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Track removed from the playlist.');
    }
}
