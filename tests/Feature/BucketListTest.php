<?php

namespace Tests\Feature;

use App\Models\BucketListItem;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BucketListTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function item(User $owner, User $partner, string $title = 'Skydive', string $status = 'planned'): BucketListItem
    {
        return BucketListItem::create([
            'user_id' => $owner->id,
            'partner_id' => $partner->id,
            'title' => $title,
            'description' => 'Together',
            'status' => $status,
            'completed_at' => $status === 'completed' ? now()->subDay() : null,
        ]);
    }

    public function test_bucket_list_pages_require_auth(): void
    {
        $this->get('/bucket-list')->assertRedirect('/login');
        $this->post('/bucket-list')->assertRedirect('/login');
    }

    public function test_index_shows_own_and_partner_items(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->item($a, $b, 'Mine');
        $this->item($b, $a, 'Theirs');

        $this->actingAs($a)->get('/bucket-list')
            ->assertOk()
            ->assertSee('Mine')
            ->assertSee('Theirs');
    }

    public function test_index_hides_items_after_disconnect(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->item($b, $a, 'Hidden Dream');
        Connection::where('sender_id', $a->id)->orWhere('receiver_id', $a->id)->delete();

        $this->actingAs($a)->get('/bucket-list')
            ->assertOk()
            ->assertDontSee('Hidden Dream');
    }

    public function test_index_hides_items_from_pending_connections(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        $this->item($b, $a, 'Pending Dream');

        $this->actingAs($a)->get('/bucket-list')
            ->assertOk()
            ->assertDontSee('Pending Dream');
    }

    public function test_index_shows_progress_counts(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->item($a, $b, 'Done One', 'completed');
        $this->item($a, $b, 'Planned One');

        $this->actingAs($a)->get('/bucket-list')
            ->assertOk()
            ->assertSee('1')
            ->assertSee('/ 2');
    }

    public function test_filter_by_status(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->item($a, $b, 'Done Item', 'completed');
        $this->item($a, $b, 'Planned Item');

        $this->actingAs($a)->get('/bucket-list?status=completed')
            ->assertOk()
            ->assertSee('Done Item')
            ->assertDontSee('Planned Item');
    }

    public function test_cannot_create_item_without_connected_partner(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)
            ->post('/bucket-list', ['partner_id' => $b->id, 'title' => 'Nope'])
            ->assertForbidden();
    }

    public function test_cannot_create_item_with_self(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/bucket-list', ['partner_id' => $a->id, 'title' => 'Nope'])
            ->assertForbidden();
    }

    public function test_create_item_validates_fields(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/bucket-list', ['partner_id' => 999999, 'title' => ''])
            ->assertSessionHasErrors(['partner_id', 'title']);
    }

    public function test_create_item_persists_creator_as_auth_user(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->actingAs($a)
            ->post('/bucket-list', ['partner_id' => $b->id, 'title' => 'See the Northern Lights'])
            ->assertRedirect('/bucket-list')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bucket_list_items', [
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'See the Northern Lights',
            'status' => 'planned',
        ]);
    }

    public function test_partner_can_toggle_item_completion(): void
    {
        [$a, $b] = $this->connectedPair();
        $item = $this->item($a, $b, 'Road trip');

        $this->actingAs($b)
            ->patch("/bucket-list/{$item->id}/toggle")
            ->assertRedirect('/bucket-list')
            ->assertSessionHas('success');

        $fresh = $item->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertNotNull($fresh->completed_at);

        $this->actingAs($b)
            ->patch("/bucket-list/{$item->id}/toggle")
            ->assertRedirect('/bucket-list');

        $fresh = $item->fresh();
        $this->assertSame('planned', $fresh->status);
        $this->assertNull($fresh->completed_at);
    }

    public function test_stranger_cannot_toggle_item(): void
    {
        [$a, $b] = $this->connectedPair();
        $item = $this->item($a, $b, 'Secret');
        $c = User::factory()->create();

        $this->actingAs($c)
            ->patch("/bucket-list/{$item->id}/toggle")
            ->assertForbidden();

        $this->assertSame('planned', $item->fresh()->status);
    }

    public function test_partner_cannot_toggle_after_disconnect(): void
    {
        [$a, $b] = $this->connectedPair();
        $item = $this->item($a, $b, 'Old Dream');
        Connection::where('sender_id', $a->id)->orWhere('receiver_id', $a->id)->delete();

        $this->actingAs($b)
            ->patch("/bucket-list/{$item->id}/toggle")
            ->assertForbidden();
    }

    public function test_only_creator_can_delete_item(): void
    {
        [$a, $b] = $this->connectedPair();
        $item = $this->item($a, $b, 'Solo Dream');

        $this->actingAs($b)->delete("/bucket-list/{$item->id}")->assertForbidden();
        $this->assertDatabaseHas('bucket_list_items', ['id' => $item->id]);

        $this->actingAs($a)->delete("/bucket-list/{$item->id}")->assertRedirect('/bucket-list');
        $this->assertDatabaseMissing('bucket_list_items', ['id' => $item->id]);
    }

    public function test_bucket_list_page_query_count_does_not_grow_with_items(): void
    {
        [$a, $b] = $this->connectedPair();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/bucket-list')->assertOk();
        $baseline = count(DB::getQueryLog());

        foreach (range(1, 8) as $i) {
            $this->item($a, $b, 'Dream '.$i);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($a)->get('/bucket-list')->assertOk();
        $withItems = count(DB::getQueryLog());

        $this->assertLessThan(5, $withItems - $baseline, "Query count grew from {$baseline} to {$withItems}");
    }
}
