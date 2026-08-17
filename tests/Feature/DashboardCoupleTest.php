<?php

namespace Tests\Feature;

use App\Models\BucketListItem;
use App\Models\Connection;
use App\Models\ImportantDate;
use App\Models\Memory;
use App\Models\PlaylistTrack;
use App\Models\SharedEvent;
use App\Models\SharedMemory;
use App\Models\SharedPlaylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardCoupleTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    public function test_dashboard_hides_overview_without_partner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('cpl-overview');
    }

    public function test_dashboard_shows_overview_with_connected_partner(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = Memory::factory()->create(['user_id' => $a->id, 'title' => 'Our memory']);
        SharedMemory::create(['memory_id' => $memory->id, 'partner_id' => $b->id]);
        SharedEvent::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Picnic',
            'event_date' => now()->addDays(3)->toDateString(),
        ]);
        $playlist = SharedPlaylist::create(['user_id' => $a->id, 'partner_id' => $b->id, 'name' => 'Our Songs']);
        PlaylistTrack::create([
            'playlist_id' => $playlist->id,
            'added_by' => $a->id,
            'title' => 'Song',
            'artist' => 'Artist',
            'url' => 'https://example.com/track',
            'position' => 0,
        ]);
        BucketListItem::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Skydive',
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);
        BucketListItem::create([
            'user_id' => $b->id,
            'partner_id' => $a->id,
            'title' => 'Road trip',
            'status' => 'planned',
        ]);

        $this->actingAs($a)->get('/dashboard')
            ->assertOk()
            ->assertSee($b->name)
            ->assertSee('Shared Memories')
            ->assertSee('1')
            ->assertSee('Shared Events')
            ->assertSee('Playlist Tracks')
            ->assertSee('Bucket List Done')
            ->assertSee('/ 2');
    }

    public function test_dashboard_counts_only_this_pairs_data(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $strangerMemory = Memory::factory()->create(['user_id' => $c->id]);
        SharedMemory::create(['memory_id' => $strangerMemory->id, 'partner_id' => $b->id]);
        SharedMemory::create(['memory_id' => $strangerMemory->id, 'partner_id' => $a->id]);
        SharedEvent::create([
            'user_id' => $c->id,
            'partner_id' => $a->id,
            'title' => 'Not ours',
            'event_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->actingAs($a)->get('/dashboard')
            ->assertOk()
            ->assertSee('Shared Memories')
            ->assertDontSee('Not ours');

        $html = $this->actingAs($a)->get('/dashboard')->getContent();
        $this->assertSame(0, substr_count($html, 'Not ours'));
    }

    public function test_dashboard_shows_upcoming_important_date(): void
    {
        [$a, $b] = $this->connectedPair();
        ImportantDate::create([
            'user_id' => $b->id,
            'partner_id' => $a->id,
            'title' => 'Anniversary',
            'date' => now()->addDays(5)->toDateString(),
            'type' => 'anniversary',
            'recurring' => true,
        ]);

        $this->actingAs($a)->get('/dashboard')
            ->assertOk()
            ->assertSee('Anniversary')
            ->assertSee('in 5 days');
    }

    public function test_dashboard_query_count_stays_bounded_with_couple_data(): void
    {
        [$a, $b] = $this->connectedPair();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/dashboard')->assertOk();
        $baseline = count(DB::getQueryLog());

        $memory = Memory::factory()->create(['user_id' => $a->id]);
        SharedMemory::create(['memory_id' => $memory->id, 'partner_id' => $b->id]);
        $playlist = SharedPlaylist::create(['user_id' => $a->id, 'partner_id' => $b->id, 'name' => 'P']);
        PlaylistTrack::create([
            'playlist_id' => $playlist->id,
            'added_by' => $a->id,
            'title' => 'T',
            'artist' => 'A',
            'url' => 'https://example.com/t',
            'position' => 0,
        ]);
        BucketListItem::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'X',
            'status' => 'planned',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/dashboard')->assertOk();
        $withData = count(DB::getQueryLog());

        $this->assertLessThan(25, $withData, "Dashboard queries grew to {$withData}");
        $this->assertLessThan(6, $withData - $baseline, "Query count grew from {$baseline} to {$withData}");
    }

    public function test_deleting_memory_flushes_partner_dashboard_cache(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = Memory::factory()->create(['user_id' => $a->id]);
        $b->favorites()->create(['memory_id' => $memory->id]);

        Cache::put('dashboard.stats.'.$b->id, ['total_favorites' => 99], 300);

        $this->actingAs($a)->delete("/memories/{$memory->id}");

        $this->assertFalse(Cache::has('dashboard.stats.'.$b->id), 'Partner dashboard cache was not flushed.');
        $this->assertFalse(Cache::has('dashboard.stats.'.$a->id), 'Owner dashboard cache was not flushed.');
    }
}
