<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\PlaylistTrack;
use App\Models\SharedPlaylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlaylistTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function playlist(User $owner, User $partner, string $name = 'Our Songs'): SharedPlaylist
    {
        return SharedPlaylist::create(['user_id' => $owner->id, 'partner_id' => $partner->id, 'name' => $name]);
    }

    private function track(SharedPlaylist $playlist, User $adder, string $title = 'Song', string $artist = 'Artist'): PlaylistTrack
    {
        return PlaylistTrack::create([
            'playlist_id' => $playlist->id,
            'added_by' => $adder->id,
            'title' => $title,
            'artist' => $artist,
            'url' => 'https://example.com/track',
            'position' => $playlist->tracks()->count(),
        ]);
    }

    public function test_playlist_pages_require_auth(): void
    {
        $this->get('/playlists')->assertRedirect('/login');
        $this->post('/playlists')->assertRedirect('/login');
    }

    public function test_partner_cannot_create_duplicate_playlist_in_reverse_direction(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->playlist($a, $b);

        $this->actingAs($b)
            ->from('/playlists')
            ->post('/playlists', ['partner_id' => $a->id, 'name' => 'Duplicate'])
            ->assertRedirect('/playlists')
            ->assertSessionHasErrors('partner_id');

        $this->assertSame(1, SharedPlaylist::count());
    }

    public function test_index_shows_own_playlists_with_tracks(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $this->track($playlist, $b, 'Our Song');

        $this->actingAs($a)->get('/playlists')
            ->assertOk()
            ->assertSee('Our Songs')
            ->assertSee('Our Song')
            ->assertSee('With '.$b->name)
            ->assertSee($b->name);
    }

    public function test_index_shows_partners_playlist_in_either_direction(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->playlist($b, $a, 'From Partner');

        $this->actingAs($a)->get('/playlists')
            ->assertOk()
            ->assertSee('From Partner')
            ->assertSee('Started by '.$b->name);
    }

    public function test_index_hides_partners_playlist_after_disconnect(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->playlist($b, $a, 'Hidden Playlist');
        Connection::where('sender_id', $a->id)->orWhere('receiver_id', $a->id)->delete();

        $this->actingAs($a)->get('/playlists')
            ->assertOk()
            ->assertDontSee('Hidden Playlist');
    }

    public function test_index_hides_playlists_from_pending_connections(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        $this->playlist($b, $a, 'Pending Playlist');

        $this->actingAs($a)->get('/playlists')
            ->assertOk()
            ->assertDontSee('Pending Playlist');
    }

    public function test_cannot_create_playlist_without_connected_partner(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)
            ->post('/playlists', ['partner_id' => $b->id, 'name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_cannot_create_playlist_with_pending_partner(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->post('/playlists', ['partner_id' => $b->id, 'name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_cannot_create_playlist_with_self(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/playlists', ['partner_id' => $a->id, 'name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_create_playlist_requires_valid_partner_and_name(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/playlists', ['partner_id' => 999999, 'name' => ''])
            ->assertSessionHasErrors(['partner_id', 'name'])
            ->assertRedirect();
    }

    public function test_create_playlist_persists_owner_as_auth_user(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->actingAs($a)
            ->post('/playlists', ['partner_id' => $b->id, 'name' => 'Our Anthem'])
            ->assertRedirect('/playlists')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shared_playlists', [
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'name' => 'Our Anthem',
        ]);
    }

    public function test_duplicate_playlist_with_same_partner_is_rejected(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->playlist($a, $b);

        $this->actingAs($a)
            ->from('/playlists')
            ->post('/playlists', ['partner_id' => $b->id, 'name' => 'Duplicate'])
            ->assertRedirect('/playlists')
            ->assertSessionHasErrors('partner_id');

        $this->assertSame(1, SharedPlaylist::where('user_id', $a->id)->count());
    }

    public function test_partner_cannot_rename_playlist(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);

        $this->actingAs($b)
            ->put("/playlists/{$playlist->id}", ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame('Our Songs', $playlist->fresh()->name);
    }

    public function test_owner_can_rename_playlist(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);

        $this->actingAs($a)
            ->put("/playlists/{$playlist->id}", ['name' => 'New Name'])
            ->assertRedirect('/playlists')
            ->assertSessionHas('success');

        $this->assertSame('New Name', $playlist->fresh()->name);
    }

    public function test_owner_can_delete_playlist_but_partner_cannot(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);

        $this->actingAs($b)->delete("/playlists/{$playlist->id}")->assertForbidden();
        $this->assertDatabaseHas('shared_playlists', ['id' => $playlist->id]);

        $this->actingAs($a)->delete("/playlists/{$playlist->id}")->assertRedirect('/playlists');
        $this->assertDatabaseMissing('shared_playlists', ['id' => $playlist->id]);
    }

    public function test_partner_can_add_track_but_stranger_cannot(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $c = User::factory()->create();

        $this->actingAs($b)
            ->post("/playlists/{$playlist->id}/tracks", [
                'title' => 'Partner Song',
                'artist' => 'Artist',
                'url' => 'https://example.com/partner',
            ])
            ->assertRedirect('/playlists')
            ->assertSessionHas('success');

        $this->actingAs($c)
            ->post("/playlists/{$playlist->id}/tracks", [
                'title' => 'Intruder',
                'artist' => 'Artist',
                'url' => 'https://example.com/intruder',
            ])
            ->assertForbidden();

        $this->assertSame(1, $playlist->tracks()->count());
        $this->assertSame($b->id, $playlist->tracks()->first()->added_by);
    }

    public function test_track_add_validates_fields(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);

        $this->actingAs($b)
            ->post("/playlists/{$playlist->id}/tracks", [
                'title' => '',
                'artist' => '',
                'url' => 'not-a-url',
            ])
            ->assertSessionHasErrors(['title', 'artist', 'url']);
    }

    public function test_cannot_add_track_duplicating_partners_track(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $this->track($playlist, $a, 'Moonlight Drive', 'The Drifters');

        $this->actingAs($b)
            ->from("/playlists/{$playlist->id}")
            ->post("/playlists/{$playlist->id}/tracks", [
                'title' => 'Moonlight Drive',
                'artist' => 'The Drifters',
                'url' => 'https://example.com/another-embed',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'This track is already in the playlist.');

        $this->assertSame(1, $playlist->tracks()->count());
    }

    public function test_cannot_add_track_duplicating_own_track_with_different_casing(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $this->track($playlist, $a, 'Moonlight Drive', 'The Drifters');

        $this->actingAs($a)
            ->from("/playlists/{$playlist->id}")
            ->post("/playlists/{$playlist->id}/tracks", [
                'title' => 'moonlight drive',
                'artist' => 'THE DRIFTERS',
                'url' => 'https://example.com/another-embed',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'This track is already in the playlist.');

        $this->assertSame(1, $playlist->tracks()->count());
    }

    public function test_cannot_remove_partners_track_but_can_remove_own(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $trackByB = $this->track($playlist, $b, 'Their Song');
        $trackByA = $this->track($playlist, $a, 'My Song');

        $this->actingAs($b)->delete("/playlists/{$playlist->id}/tracks/{$trackByA->id}")->assertForbidden();
        $this->assertDatabaseHas('playlist_tracks', ['id' => $trackByA->id]);

        $this->actingAs($b)->delete("/playlists/{$playlist->id}/tracks/{$trackByB->id}")->assertRedirect('/playlists');
        $this->assertDatabaseMissing('playlist_tracks', ['id' => $trackByB->id]);
    }

    public function test_owner_can_remove_any_track(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $track = $this->track($playlist, $b, 'Their Song');

        $this->actingAs($a)->delete("/playlists/{$playlist->id}/tracks/{$track->id}")->assertRedirect('/playlists');

        $this->assertDatabaseMissing('playlist_tracks', ['id' => $track->id]);
    }

    public function test_stranger_cannot_view_playlist_edit_page(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);
        $c = User::factory()->create();

        $this->actingAs($b)->get("/playlists/{$playlist->id}/edit")->assertForbidden();
        $this->actingAs($c)->get("/playlists/{$playlist->id}/edit")->assertForbidden();
    }

    public function test_playlist_page_query_count_does_not_grow_with_tracks(): void
    {
        [$a, $b] = $this->connectedPair();
        $playlist = $this->playlist($a, $b);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/playlists')->assertOk();
        $baseline = count(DB::getQueryLog());

        foreach (range(1, 6) as $i) {
            $this->track($playlist, $b, 'Song '.$i);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/playlists')->assertOk();
        $withItems = count(DB::getQueryLog());

        $this->assertLessThan(5, $withItems - $baseline, "Query count grew from {$baseline} to {$withItems}");
    }
}
