<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Memory;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SharedMemoryTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function memoryFor(User $owner, array $overrides = []): Memory
    {
        return Memory::factory()->create(array_merge(['user_id' => $owner->id], $overrides));
    }

    private function share(User $owner, User $partner, Memory $memory): TestResponse
    {
        return $this->actingAs($owner)->post("/memories/{$memory->id}/share", [
            'partner_id' => $partner->id,
        ]);
    }

    private function shareBetween(User $owner, User $partner, Memory $memory): SharedMemory
    {
        return SharedMemory::create([
            'memory_id' => $memory->id,
            'partner_id' => $partner->id,
        ]);
    }

    // ---------- SHARE (create) ----------

    public function test_owner_can_share_memory_with_connected_partner(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        $this->share($a, $b, $memory)
            ->assertRedirect("/memories/{$memory->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shared_memories', [
            'memory_id' => $memory->id,
            'partner_id' => $b->id,
        ]);
    }

    public function test_can_share_with_partner_when_connection_is_in_either_direction(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        $memory = $this->memoryFor($a);

        $this->share($a, $b, $memory)->assertRedirect("/memories/{$memory->id}");

        $this->assertDatabaseHas('shared_memories', [
            'memory_id' => $memory->id,
            'partner_id' => $b->id,
        ]);
    }

    public function test_non_owner_cannot_share(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        $this->share($b, $a, $memory)->assertForbidden();

        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    public function test_cannot_share_with_unconnected_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $memory = $this->memoryFor($owner);

        $this->share($owner, $stranger, $memory)->assertForbidden();

        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    public function test_cannot_share_with_pending_or_rejected_connection(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Connection::factory()->pending()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $memory = $this->memoryFor($owner);
        $this->share($owner, $other, $memory)->assertForbidden();
        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);

        Connection::query()->delete();
        Connection::factory()->rejected()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $this->share($owner, $other, $memory)->assertForbidden();
        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    public function test_cannot_share_memory_with_self(): void
    {
        $owner = User::factory()->create();
        $memory = $this->memoryFor($owner);

        $this->share($owner, $owner, $memory)->assertForbidden();

        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    public function test_cannot_share_the_same_memory_twice_with_same_partner(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->share($a, $b, $memory)->assertForbidden();

        $this->assertDatabaseCount('shared_memories', 1);
    }

    public function test_share_form_renders_for_owner(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        $this->actingAs($a)->get("/memories/{$memory->id}/share")
            ->assertOk()
            ->assertSee($memory->title)
            ->assertSee($b->name);
    }

    public function test_share_form_is_forbidden_for_non_owner(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        $this->actingAs($b)->get("/memories/{$memory->id}/share")->assertForbidden();
    }

    public function test_share_form_hides_already_shared_partners(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($a)->get("/memories/{$memory->id}/share")
            ->assertOk()
            ->assertDontSee($b->name);
    }

    public function test_share_requires_valid_partner_id(): void
    {
        $owner = User::factory()->create();
        $memory = $this->memoryFor($owner);

        $this->actingAs($owner)->post("/memories/{$memory->id}/share", [])
            ->assertSessionHasErrors('partner_id');

        $this->actingAs($owner)->post("/memories/{$memory->id}/share", ['partner_id' => 999999])
            ->assertSessionHasErrors('partner_id');
    }

    // ---------- ACCESS (view) ----------

    public function test_partner_can_view_shared_memory(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($b)->get("/memories/{$memory->id}")
            ->assertOk()
            ->assertSee($memory->title);
    }

    public function test_partner_cannot_view_memory_before_share(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        $this->actingAs($b)->get("/memories/{$memory->id}")->assertForbidden();
    }

    public function test_stranger_cannot_view_shared_memory(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($stranger)->get("/memories/{$memory->id}")->assertForbidden();
    }

    public function test_partner_cannot_view_another_users_private_memory(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        $memory = $this->memoryFor($c);

        $this->actingAs($b)->get("/memories/{$memory->id}")->assertForbidden();
    }

    public function test_partner_cannot_view_memory_shared_only_with_another_partner(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($c)->get("/memories/{$memory->id}")->assertForbidden();
    }

    public function test_partner_cannot_view_memory_image_before_share(): void
    {
        Storage::fake('private');

        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a, ['image' => 'memories/secret.jpg']);
        Storage::disk('private')->put('memories/secret.jpg', 'img');

        $this->actingAs($b)->get("/memories/{$memory->id}/image")->assertForbidden();
    }

    public function test_partner_can_view_memory_image_after_share(): void
    {
        Storage::fake('private');

        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a, ['image' => 'memories/secret.jpg']);
        Storage::disk('private')->put('memories/secret.jpg', 'img');
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($b)->get("/memories/{$memory->id}/image")->assertOk();
    }

    public function test_partner_cannot_update_or_delete_shared_memory(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($b)->get("/memories/{$memory->id}/edit")->assertForbidden();
        $this->actingAs($b)->put("/memories/{$memory->id}", [
            'title' => 'hacked',
            'description' => 'tampered',
            'memory_date' => '2024-01-01',
        ])->assertForbidden();
        $this->actingAs($b)->delete("/memories/{$memory->id}")->assertForbidden();
    }

    // ---------- UNSHARE (destroy) ----------

    public function test_owner_can_unshare_memory_without_deleting_it(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $shared = $memory->sharedWith()->first();

        $this->actingAs($a)->delete("/shared-memories/{$shared->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('shared_memories', ['id' => $shared->id]);
        $this->assertDatabaseHas('memories', ['id' => $memory->id]);
    }

    public function test_partner_cannot_unshare(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $shared = $memory->sharedWith()->first();

        $this->actingAs($b)->delete("/shared-memories/{$shared->id}")->assertForbidden();

        $this->assertDatabaseHas('shared_memories', ['id' => $shared->id]);
    }

    public function test_stranger_cannot_unshare(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $shared = $memory->sharedWith()->first();

        $this->actingAs($stranger)->delete("/shared-memories/{$shared->id}")->assertForbidden();
    }

    public function test_unshared_partner_loses_access_immediately(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $shared = $this->shareBetween($a, $b, $memory);

        $this->actingAs($b)->get("/memories/{$memory->id}")->assertOk();

        $this->actingAs($a)->delete("/shared-memories/{$shared->id}");

        $this->actingAs($b)->get("/memories/{$memory->id}")->assertForbidden();
    }

    // ---------- DISCONNECT REVOKES ACCESS ----------

    public function test_disconnecting_revokes_all_shared_memory_access_between_pair(): void
    {
        [$a, $b] = $this->connectedPair();
        $memoryA = $this->memoryFor($a);
        $memoryB = $this->memoryFor($b);
        $this->shareBetween($a, $b, $memoryA);
        $this->shareBetween($b, $a, $memoryB);

        $connection = Connection::where('sender_id', $a->id)->first();

        $this->actingAs($a)->delete("/connections/{$connection->id}");

        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memoryA->id]);
        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memoryB->id]);

        $this->actingAs($b)->get("/memories/{$memoryA->id}")->assertForbidden();
        $this->actingAs($a)->get("/memories/{$memoryB->id}")->assertForbidden();
    }

    // ---------- INDEX ----------

    public function test_index_hides_shares_from_unconnected_users(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $strangerMemory = $this->memoryFor($stranger, ['title' => 'Secret from stranger']);
        $this->shareBetween($stranger, $b, $strangerMemory);

        $this->actingAs($b)->get('/shared-memories')
            ->assertOk()
            ->assertDontSee('Secret from stranger');
    }

    public function test_index_shows_shared_with_me_and_shared_by_me(): void
    {
        [$a, $b] = $this->connectedPair();
        $memoryA = $this->memoryFor($a, ['title' => 'Beach day']);
        $memoryB = $this->memoryFor($b, ['title' => 'Coffee run']);
        $this->shareBetween($a, $b, $memoryA);
        $this->shareBetween($b, $a, $memoryB);

        $this->actingAs($a)->get('/shared-memories')
            ->assertOk()
            ->assertSee('Beach day')
            ->assertSee('Coffee run')
            ->assertSee($b->name);
    }

    public function test_index_filter_by_partner_only_shows_that_partners_shares(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $memoryB = $this->memoryFor($b, ['title' => 'With B']);
        $memoryC = $this->memoryFor($c, ['title' => 'With C']);
        $this->shareBetween($b, $a, $memoryB);
        $this->shareBetween($c, $a, $memoryC);

        $this->actingAs($a)->get("/shared-memories?partner={$b->id}")
            ->assertOk()
            ->assertSee('With B')
            ->assertDontSee('With C');
    }

    public function test_index_ignores_invalid_partner_filter(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $memory = $this->memoryFor($b, ['title' => 'Beach day']);
        $this->shareBetween($b, $a, $memory);

        $this->actingAs($a)->get("/shared-memories?partner={$stranger->id}")
            ->assertOk()
            ->assertSee('Beach day');
    }

    public function test_index_does_not_leak_memories_shared_with_other_people(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $memoryForB = $this->memoryFor($a, ['title' => 'For B']);
        $memoryForC = $this->memoryFor($a, ['title' => 'Only for C']);
        $this->shareBetween($a, $b, $memoryForB);
        $this->shareBetween($a, $c, $memoryForC);

        $this->actingAs($b)->get('/shared-memories')
            ->assertOk()
            ->assertSee('For B')
            ->assertDontSee('Only for C');
    }

    // ---------- CRUD REGRESSION ----------

    public function test_owner_crud_still_works_with_shared_memory(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a, ['title' => 'Original']);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($a)->put("/memories/{$memory->id}", [
            'title' => 'Updated',
            'description' => 'still ours',
            'memory_date' => '2024-01-01',
        ])->assertRedirect();
        $this->actingAs($a)->get("/memories/{$memory->id}")->assertOk()->assertSee('Updated');

        $this->actingAs($a)->delete("/memories/{$memory->id}")->assertRedirect();

        $this->assertDatabaseMissing('memories', ['id' => $memory->id]);
        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    public function test_deleting_memory_cascades_to_shared_rows(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);
        $this->shareBetween($a, $b, $memory);

        $this->actingAs($a)->delete("/memories/{$memory->id}");

        $this->assertDatabaseMissing('shared_memories', ['memory_id' => $memory->id]);
    }

    // ---------- PERFORMANCE ----------

    public function test_index_does_not_trigger_n_plus_one_queries(): void
    {
        [$a, $b] = $this->connectedPair();
        for ($i = 0; $i < 3; $i++) {
            $this->shareBetween($a, $b, $this->memoryFor($a, ['title' => "From A $i"]));
            $this->shareBetween($b, $a, $this->memoryFor($b, ['title' => "From B $i"]));
        }

        DB::enableQueryLog();

        $this->actingAs($b)->get('/shared-memories')->assertOk();

        $queries = collect(DB::getQueryLog())
            ->filter(fn ($query) => str_contains($query['query'], 'select'))
            ->count();

        $this->assertLessThan(25, $queries);
    }

    public function test_share_action_does_not_trigger_n_plus_one_queries(): void
    {
        [$a, $b] = $this->connectedPair();
        $memory = $this->memoryFor($a);

        DB::enableQueryLog();

        $this->share($a, $b, $memory)->assertRedirect();

        $queries = collect(DB::getQueryLog())->count();

        $this->assertLessThan(15, $queries);
    }
}
