<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\ImportantDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ImportantDateTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function createDate(User $owner, ?User $partner, array $overrides = []): ImportantDate
    {
        return ImportantDate::create(array_merge([
            'user_id' => $owner->id,
            'partner_id' => $partner?->id,
            'title' => 'Anniversary',
            'date' => '2025-02-14',
            'type' => 'anniversary',
            'description' => null,
            'recurring' => true,
        ], $overrides));
    }

    private function storeDate(User $owner, array $overrides = []): TestResponse
    {
        return $this->actingAs($owner)->post('/important-dates', array_merge([
            'partner_id' => null,
            'title' => 'Anniversary',
            'date' => '2025-02-14',
            'type' => 'anniversary',
            'description' => 'The day it all began.',
            'recurring' => '1',
        ], $overrides));
    }

    // ---------- CREATE ----------

    public function test_user_can_create_personal_date(): void
    {
        $user = User::factory()->create();

        $this->storeDate($user)
            ->assertRedirect('/important-dates')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('important_dates', [
            'user_id' => $user->id,
            'partner_id' => null,
            'title' => 'Anniversary',
            'date' => '2025-02-14 00:00:00',
            'type' => 'anniversary',
            'recurring' => 1,
        ]);
    }

    public function test_user_can_create_shared_date_with_connected_partner(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->storeDate($a, ['partner_id' => $b->id])
            ->assertRedirect('/important-dates');

        $this->assertDatabaseHas('important_dates', [
            'user_id' => $a->id,
            'partner_id' => $b->id,
        ]);
    }

    public function test_cannot_share_date_with_unconnected_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->storeDate($owner, ['partner_id' => $stranger->id])->assertForbidden();

        $this->assertDatabaseCount('important_dates', 0);
    }

    public function test_cannot_share_date_with_pending_or_rejected_connection(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Connection::factory()->pending()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $this->storeDate($owner, ['partner_id' => $other->id])->assertForbidden();

        Connection::query()->delete();
        Connection::factory()->rejected()->create(['sender_id' => $owner->id, 'receiver_id' => $other->id]);
        $this->storeDate($owner, ['partner_id' => $other->id])->assertForbidden();

        $this->assertDatabaseCount('important_dates', 0);
    }

    public function test_cannot_share_date_with_self(): void
    {
        $user = User::factory()->create();

        $this->storeDate($user, ['partner_id' => $user->id])->assertForbidden();
    }

    public function test_date_creation_validates_input(): void
    {
        $user = User::factory()->create();

        $this->storeDate($user, ['title' => '', 'date' => '', 'type' => ''])
            ->assertSessionHasErrors(['title', 'date', 'type']);

        $this->storeDate($user, ['type' => 'hacked'])->assertSessionHasErrors('type');
    }

    public function test_partner_is_notified_when_shared_date_is_created(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->storeDate($a, ['partner_id' => $b->id])->assertRedirect();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $b->id]);
    }

    public function test_no_notification_for_personal_date(): void
    {
        $user = User::factory()->create();

        $this->storeDate($user)->assertRedirect();

        $this->assertDatabaseCount('notifications', 0);
    }

    // ---------- VISIBILITY ----------

    public function test_partner_sees_shared_date_on_index(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->createDate($a, $b, ['title' => 'First kiss']);

        $this->actingAs($b)->get('/important-dates')
            ->assertOk()
            ->assertSee('First kiss');
    }

    public function test_stranger_does_not_see_date(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $this->createDate($a, $b, ['title' => 'Secret day']);

        $this->actingAs($stranger)->get('/important-dates')
            ->assertOk()
            ->assertDontSee('Secret day');
    }

    public function test_owner_sees_own_personal_date(): void
    {
        $user = User::factory()->create();
        $this->createDate($user, null, ['title' => 'My birthday']);

        $this->actingAs($user)->get('/important-dates')
            ->assertOk()
            ->assertSee('My birthday');
    }

    public function test_partner_does_not_see_owners_personal_date(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->createDate($a, null, ['title' => 'Private note']);

        $this->actingAs($b)->get('/important-dates')
            ->assertOk()
            ->assertDontSee('Private note');
    }

    public function test_disconnected_partner_loses_date_visibility(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->createDate($a, $b, ['title' => 'Once shared']);
        $connection = Connection::where('sender_id', $a->id)->first();

        $this->actingAs($a)->delete("/connections/{$connection->id}");

        $this->actingAs($b)->get('/important-dates')
            ->assertOk()
            ->assertDontSee('Once shared');
    }

    public function test_connected_third_user_does_not_see_date_between_pair(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        $this->createDate($a, $b, ['title' => 'Only for two']);

        $this->actingAs($c)->get('/important-dates')
            ->assertOk()
            ->assertDontSee('Only for two');
    }

    // ---------- EDIT / DELETE ----------

    public function test_owner_can_edit_date(): void
    {
        $user = User::factory()->create();
        $date = $this->createDate($user, null);

        $this->actingAs($user)->put("/important-dates/{$date->id}", [
            'title' => 'Renamed',
            'date' => '2025-03-01',
            'type' => 'birthday',
            'description' => null,
        ])->assertRedirect('/important-dates');

        $this->assertDatabaseHas('important_dates', [
            'id' => $date->id,
            'title' => 'Renamed',
            'date' => '2025-03-01 00:00:00',
            'type' => 'birthday',
            'recurring' => 0,
        ]);
    }

    public function test_partner_cannot_edit_date(): void
    {
        [$a, $b] = $this->connectedPair();
        $date = $this->createDate($a, $b);

        $this->actingAs($b)->get("/important-dates/{$date->id}/edit")->assertForbidden();
        $this->actingAs($b)->put("/important-dates/{$date->id}", [
            'title' => 'hacked',
            'date' => '2025-03-01',
            'type' => 'custom',
        ])->assertForbidden();
        $this->actingAs($b)->delete("/important-dates/{$date->id}")->assertForbidden();
    }

    public function test_stranger_cannot_edit_or_delete_date(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $date = $this->createDate($user, null);

        $this->actingAs($stranger)->get("/important-dates/{$date->id}/edit")->assertForbidden();
        $this->actingAs($stranger)->delete("/important-dates/{$date->id}")->assertForbidden();
    }

    public function test_owner_can_delete_date(): void
    {
        $user = User::factory()->create();
        $date = $this->createDate($user, null);

        $this->actingAs($user)->delete("/important-dates/{$date->id}")
            ->assertRedirect('/important-dates')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('important_dates', ['id' => $date->id]);
    }

    // ---------- COUNTDOWN ----------

    public function test_index_shows_countdown_for_upcoming_date(): void
    {
        $user = User::factory()->create();
        $date = $this->createDate($user, null, [
            'title' => 'Upcoming trip',
            'date' => now()->addDays(12)->toDateString(),
            'recurring' => false,
        ]);

        $this->actingAs($user)->get('/important-dates')
            ->assertOk()
            ->assertSee('Upcoming trip')
            ->assertSee('12 days');
    }

    public function test_index_marks_past_non_recurring_date(): void
    {
        $user = User::factory()->create();
        $this->createDate($user, null, [
            'title' => 'Old day',
            'date' => now()->subDays(5)->toDateString(),
            'recurring' => false,
        ]);

        $this->actingAs($user)->get('/important-dates')
            ->assertOk()
            ->assertSee('Old day')
            ->assertSee('Past date');
    }

    public function test_recurring_past_date_gets_next_year_countdown(): void
    {
        $user = User::factory()->create();
        $this->createDate($user, null, [
            'title' => 'Anniversary',
            'date' => now()->subDays(100)->toDateString(),
            'recurring' => true,
        ]);

        $this->actingAs($user)->get('/important-dates')
            ->assertOk()
            ->assertSee('Anniversary')
            ->assertSee('days');
    }

    // ---------- PERFORMANCE ----------

    public function test_index_page_query_count_stays_bounded(): void
    {
        [$a, $b] = $this->connectedPair();
        foreach (range(1, 10) as $i) {
            $this->createDate($a, $b, ['title' => 'Date '.$i, 'date' => '2025-0'.($i % 9 + 1).'-10']);
        }

        DB::enableQueryLog();

        $this->actingAs($b)->get('/important-dates')->assertOk();

        $queries = count(DB::getQueryLog());

        $this->assertLessThan(15, $queries, "Expected bounded queries, got {$queries}");
    }
}
