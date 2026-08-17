<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\SharedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SharedEventTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function createEvent(User $owner, User $partner, array $overrides = []): SharedEvent
    {
        return SharedEvent::create(array_merge([
            'user_id' => $owner->id,
            'partner_id' => $partner->id,
            'title' => 'Date night',
            'description' => null,
            'event_date' => '2025-02-14',
            'event_time' => null,
            'location' => null,
            'color' => null,
        ], $overrides));
    }

    private function storeEvent(User $owner, ?int $partnerId, array $overrides = []): TestResponse
    {
        return $this->actingAs($owner)->post('/calendar/events', array_merge([
            'partner_id' => $partnerId,
            'title' => 'Date night',
            'description' => 'Candlelight dinner.',
            'event_date' => '2025-02-14',
            'event_time' => '19:30',
            'location' => 'La Trattoria',
            'color' => '#e8386a',
        ], $overrides));
    }

    // ---------- CREATE ----------

    public function test_owner_can_create_shared_event_with_connected_partner(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->storeEvent($a, $b->id)
            ->assertRedirect('/calendar/events/1')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shared_events', [
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Date night',
            'event_date' => '2025-02-14 00:00:00',
        ]);

        $event = SharedEvent::first();
        $this->assertSame('19:30', $event->event_time->format('H:i'));
        $this->assertDatabaseHas('shared_events', ['title' => 'Date night', 'event_time' => '19:30']);
    }

    public function test_can_create_event_with_connection_in_either_direction(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);

        $this->storeEvent($a, $b->id)->assertRedirect('/calendar/events/1');
    }

    public function test_cannot_create_event_with_unconnected_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->storeEvent($owner, $stranger->id)->assertForbidden();

        $this->assertDatabaseCount('shared_events', 0);
    }

    public function test_cannot_create_event_with_pending_or_rejected_connection(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Connection::factory()->pending()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $this->storeEvent($owner, $other->id)->assertForbidden();

        Connection::query()->delete();
        Connection::factory()->rejected()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $this->storeEvent($owner, $other->id)->assertForbidden();

        $this->assertDatabaseCount('shared_events', 0);
    }

    public function test_cannot_create_event_with_self(): void
    {
        $owner = User::factory()->create();

        $this->storeEvent($owner, $owner->id)->assertForbidden();
    }

    public function test_event_creation_validates_input(): void
    {
        $owner = User::factory()->create();

        $this->storeEvent($owner, null, ['title' => '', 'event_date' => ''])
            ->assertSessionHasErrors(['title', 'event_date', 'partner_id']);

        $this->storeEvent($owner, 999999)
            ->assertSessionHasErrors('partner_id');

        $this->storeEvent($owner, 1, ['event_time' => 'not-a-time'])
            ->assertSessionHasErrors('event_time');
    }

    public function test_create_page_lists_partners(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->actingAs($a)->get('/calendar/events/create')
            ->assertOk()
            ->assertSee($b->name);
    }

    // ---------- ACCESS ----------

    public function test_partner_can_view_shared_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $event = $this->createEvent($a, $b);

        $this->actingAs($b)->get("/calendar/events/{$event->id}")
            ->assertOk()
            ->assertSee('Date night');
    }

    public function test_stranger_cannot_view_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $event = $this->createEvent($a, $b);

        $this->actingAs($stranger)->get("/calendar/events/{$event->id}")->assertForbidden();
    }

    public function test_connected_third_user_cannot_view_event_between_pair(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $event = $this->createEvent($a, $b);

        $this->actingAs($c)->get("/calendar/events/{$event->id}")->assertForbidden();
    }

    public function test_disconnected_partner_loses_event_access(): void
    {
        [$a, $b] = $this->connectedPair();
        $event = $this->createEvent($a, $b);
        $connection = Connection::where('sender_id', $a->id)->first();

        $this->actingAs($a)->delete("/connections/{$connection->id}");

        $this->actingAs($b)->get("/calendar/events/{$event->id}")->assertForbidden();
        $this->actingAs($b)->get('/calendar')->assertDontSee('Date night');
    }

    // ---------- UPDATE / DELETE ----------

    public function test_partner_cannot_edit_or_delete_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $event = $this->createEvent($a, $b);

        $this->actingAs($b)->get("/calendar/events/{$event->id}/edit")->assertForbidden();
        $this->actingAs($b)->put("/calendar/events/{$event->id}", [
            'title' => 'hacked',
            'description' => null,
            'event_date' => '2025-02-14',
            'event_time' => null,
            'location' => null,
            'color' => null,
        ])->assertForbidden();
        $this->actingAs($b)->delete("/calendar/events/{$event->id}")->assertForbidden();
    }

    public function test_owner_can_update_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $event = $this->createEvent($a, $b);

        $this->actingAs($a)->put("/calendar/events/{$event->id}", [
            'title' => 'New plan',
            'description' => 'Changed.',
            'event_date' => '2025-03-01',
            'event_time' => '18:00',
            'location' => 'Downtown',
            'color' => '#7c6ae8',
        ])->assertRedirect("/calendar/events/{$event->id}");

        $this->assertDatabaseHas('shared_events', [
            'id' => $event->id,
            'title' => 'New plan',
            'event_date' => '2025-03-01 00:00:00',
        ]);
    }

    public function test_owner_can_delete_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $event = $this->createEvent($a, $b);

        $this->actingAs($a)->delete("/calendar/events/{$event->id}")
            ->assertRedirect('/calendar')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('shared_events', ['id' => $event->id]);
    }

    // ---------- CALENDAR INTEGRATION ----------

    public function test_calendar_shows_event_for_owner_and_partner(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->createEvent($a, $b, ['title' => 'Anniversary trip', 'event_date' => '2025-06-15']);

        $this->actingAs($a)->get('/calendar?month=2025-06')->assertOk()->assertSee('Anniversary trip');
        $this->actingAs($b)->get('/calendar?month=2025-06')->assertOk()->assertSee('Anniversary trip');
    }

    public function test_stranger_calendar_does_not_show_event(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $this->createEvent($a, $b, ['title' => 'Secret plan', 'event_date' => '2025-06-15']);

        $this->actingAs($stranger)->get('/calendar?month=2025-06')
            ->assertOk()
            ->assertDontSee('Secret plan');
    }

    public function test_on_date_endpoint_includes_events(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->createEvent($a, $b, ['title' => 'Movie night', 'event_date' => '2025-06-15']);

        $this->actingAs($b)->getJson('/calendar/date?date=2025-06-15')
            ->assertOk()
            ->assertJson(['date' => '2025-06-15'])
            ->assertSee('Movie night');
    }

    // ---------- NOTIFICATIONS ----------

    public function test_partner_is_notified_when_event_is_created(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->storeEvent($a, $b->id)->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $b->id,
        ]);
    }

    // ---------- PERFORMANCE ----------

    public function test_calendar_page_query_count_stays_bounded(): void
    {
        [$a, $b] = $this->connectedPair();
        foreach (range(1, 12) as $i) {
            $this->createEvent($a, $b, ['title' => 'Event '.$i, 'event_date' => '2025-06-'.$i]);
        }

        DB::enableQueryLog();

        $this->actingAs($b)->get('/calendar?month=2025-06')->assertOk();

        $queries = count(DB::getQueryLog());

        $this->assertLessThan(20, $queries, "Expected bounded queries, got {$queries}");
    }
}
