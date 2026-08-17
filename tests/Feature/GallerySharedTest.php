<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Memory;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GallerySharedTest extends TestCase
{
    use RefreshDatabase;

    private function memory(User $owner, string $title = 'Photo'): Memory
    {
        return Memory::factory()->create([
            'user_id' => $owner->id,
            'title' => $title,
            'image' => 'memories/photo-'.substr(md5($title), 0, 8).'.png',
            'memory_date' => '2024-01-01',
        ]);
    }

    private function shareWith(User $owner, User $partner, Memory $memory): SharedMemory
    {
        return SharedMemory::create(['memory_id' => $memory->id, 'partner_id' => $partner->id]);
    }

    public function test_gallery_shows_own_and_shared_memories(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        $own = $this->memory($a, 'Mine');
        $shared = $this->memory($b, 'Theirs');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertSee('Mine')
            ->assertSee('Theirs')
            ->assertSee('Shared by '.$b->name);
    }

    public function test_gallery_does_not_show_unshared_memories(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        $secret = $this->memory($b, 'Private shot');

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertDontSee('Private shot');

        $this->actingAs($b)->get('/gallery')
            ->assertOk()
            ->assertSee('Private shot');
    }

    public function test_gallery_shows_shared_memory_in_either_connection_direction(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        $shared = $this->memory($b, 'Flip shot');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertSee('Flip shot');
    }

    public function test_gallery_ignores_shares_from_pending_or_rejected_connections(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        $shared = $this->memory($b, 'Early peek');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertDontSee('Early peek');
    }

    public function test_gallery_ignores_shares_from_strangers(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $shared = $this->memory($b, 'Stranger shot');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertDontSee('Stranger shot');
    }

    public function test_own_photos_do_not_show_shared_badge(): void
    {
        $a = User::factory()->create();
        $this->memory($a, 'Solo');

        $this->actingAs($a)->get('/gallery')
            ->assertOk()
            ->assertDontSee('Shared by');
    }

    public function test_partner_can_download_shared_memory_photo(): void
    {
        Storage::fake('private');
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        $shared = $this->memory($b, 'Download me');
        Storage::disk('private')->put($shared->image, 'fake image bytes');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)->get("/gallery/{$shared->id}/download")
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_stranger_cannot_download_shared_memory_photo(): void
    {
        Storage::fake('private');
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $shared = $this->memory($b, 'Keep out');
        Storage::disk('private')->put($shared->image, 'fake image bytes');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($c)->get("/gallery/{$shared->id}/download")
            ->assertForbidden();
    }

    public function test_gallery_load_more_ajax_returns_shared_memories(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        $shared = $this->memory($b, 'Ajax shot');
        $this->shareWith($b, $a, $shared);

        $this->actingAs($a)
            ->getJson('/gallery?page=1')
            ->assertOk()
            ->assertJsonPath('hasMore', false)
            ->assertSee('Ajax shot')
            ->assertSee('Shared by '.$b->name);
    }

    public function test_gallery_query_count_does_not_grow_with_shared_photos(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        $this->memory($a, 'Mine');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/gallery')->assertOk();
        $baseline = count(DB::getQueryLog());

        foreach (range(1, 8) as $i) {
            $shared = $this->memory($b, 'Shared '.$i);
            $this->shareWith($b, $a, $shared);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/gallery')->assertOk();
        $withItems = count(DB::getQueryLog());

        $this->assertLessThan(5, $withItems - $baseline, "Query count grew from {$baseline} to {$withItems}");
    }
}
