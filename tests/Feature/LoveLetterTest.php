<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\LoveLetter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LoveLetterTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function sendLetter(User $sender, ?int $receiverId, array $overrides = []): TestResponse
    {
        return $this->actingAs($sender)->post('/letters', array_merge([
            'title' => 'My dearest',
            'content' => '<p>You make my heart sing.</p>',
            'mood' => 'love',
            'letter_date' => '2024-12-15',
            'receiver_id' => $receiverId,
        ], $overrides));
    }

    private function letterBetween(User $sender, User $receiver, array $overrides = []): LoveLetter
    {
        return LoveLetter::create(array_merge([
            'user_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'For you',
            'content' => '<p>My words for you.</p>',
            'mood' => 'romantic',
            'letter_date' => '2024-12-15',
        ], $overrides));
    }

    // ---------- SEND ----------

    public function test_connected_user_can_send_love_letter(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->sendLetter($a, $b->id)
            ->assertRedirect('/letters/1')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('love_letters', [
            'user_id' => $a->id,
            'receiver_id' => $b->id,
            'title' => 'My dearest',
            'read_at' => null,
        ]);
    }

    public function test_user_can_send_to_partner_in_either_connection_direction(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);

        $this->sendLetter($a, $b->id)->assertRedirect('/letters/1');

        $this->assertDatabaseHas('love_letters', ['user_id' => $a->id, 'receiver_id' => $b->id]);
    }

    public function test_cannot_send_to_non_connected_user(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->sendLetter($a, $b->id)->assertForbidden();

        $this->assertDatabaseCount('love_letters', 0);
    }

    public function test_cannot_send_to_pending_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->sendLetter($a, $b->id)->assertForbidden();

        $this->assertDatabaseCount('love_letters', 0);
    }

    public function test_cannot_send_to_rejected_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->sendLetter($a, $b->id)->assertForbidden();

        $this->assertDatabaseCount('love_letters', 0);
    }

    public function test_cannot_send_to_self(): void
    {
        $a = User::factory()->create();

        $this->sendLetter($a, $a->id)->assertForbidden();

        $this->assertDatabaseCount('love_letters', 0);
    }

    public function test_sender_is_always_authenticated_user(): void
    {
        [$a, $b] = $this->connectedPair();
        $evil = User::factory()->create();

        $this->sendLetter($a, $b->id, ['user_id' => $evil->id])
            ->assertRedirect('/letters/1');

        $this->assertDatabaseHas('love_letters', ['user_id' => $a->id, 'receiver_id' => $b->id]);
        $this->assertDatabaseMissing('love_letters', ['user_id' => $evil->id]);
    }

    public function test_receiver_cannot_be_spoofed_to_unconnected_user(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();

        $this->sendLetter($a, $c->id)->assertForbidden();

        $this->assertDatabaseCount('love_letters', 0);
        $this->assertDatabaseMissing('love_letters', ['receiver_id' => $c->id]);
    }

    public function test_personal_letter_without_receiver_still_works(): void
    {
        $a = User::factory()->create();

        $this->sendLetter($a, null)->assertRedirect('/letters/1');

        $this->assertDatabaseHas('love_letters', ['user_id' => $a->id, 'receiver_id' => null]);
    }

    // ---------- LIST ----------

    public function test_user_sees_own_received_letters(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->letterBetween($b, $a, ['title' => 'A secret note']);

        $response = $this->actingAs($a)->get('/letters');

        $response->assertOk();
        $response->assertSee('Received');
        $response->assertSee('A secret note');
        $response->assertSee($b->name);
    }

    public function test_user_sees_own_sent_letters(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->letterBetween($a, $b, ['title' => 'To my partner']);

        $response = $this->actingAs($a)->get('/letters');

        $response->assertOk();
        $response->assertSee('To my partner');
        $response->assertSee($b->name);
    }

    public function test_received_letter_shows_new_badge_until_read(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->letterBetween($b, $a);

        $this->actingAs($a)->get('/letters')->assertSee('New');

        $this->actingAs($a)->get('/letters')->assertSee('New');
    }

    // ---------- READ STATUS ----------

    public function test_receiver_viewing_letter_marks_it_as_read(): void
    {
        [$a, $b] = $this->connectedPair();
        $letter = $this->letterBetween($a, $b);

        $this->assertNull($letter->read_at);

        $this->actingAs($b)->get("/letters/{$letter->id}")->assertOk();

        $this->assertNotNull($letter->fresh()->read_at);
    }

    public function test_sender_viewing_own_letter_does_not_mark_read(): void
    {
        [$a, $b] = $this->connectedPair();
        $letter = $this->letterBetween($a, $b);

        $this->actingAs($a)->get("/letters/{$letter->id}")->assertOk();

        $this->assertNull($letter->fresh()->read_at);
    }

    // ---------- AUTHORIZATION ----------

    public function test_user_cannot_access_letter_they_are_not_part_of(): void
    {
        [$a, $b] = $this->connectedPair();
        [$c, $d] = $this->connectedPair();
        $letter = $this->letterBetween($c, $d);

        $this->actingAs($a)->get("/letters/{$letter->id}")->assertForbidden();
        $this->actingAs($b)->get("/letters/{$letter->id}")->assertForbidden();
    }

    public function test_receiver_cannot_edit_delete_or_pin_received_letter(): void
    {
        [$a, $b] = $this->connectedPair();
        $letter = $this->letterBetween($a, $b);

        $this->actingAs($b)
            ->patch("/letters/{$letter->id}", [
                'title' => 'Hacked',
                'content' => 'x',
                'mood' => 'love',
                'letter_date' => '2024-01-01',
            ])
            ->assertForbidden();

        $this->actingAs($b)->delete("/letters/{$letter->id}")->assertForbidden();
        $this->actingAs($b)->post("/letters/{$letter->id}/pin")->assertForbidden();

        $this->assertDatabaseHas('love_letters', ['id' => $letter->id]);
    }

    public function test_sender_can_delete_own_sent_letter(): void
    {
        [$a, $b] = $this->connectedPair();
        $letter = $this->letterBetween($a, $b);

        $this->actingAs($a)->delete("/letters/{$letter->id}")->assertRedirect('/letters');

        $this->assertDatabaseMissing('love_letters', ['id' => $letter->id]);
    }

    // ---------- PERFORMANCE ----------

    public function test_letters_index_has_no_n_plus_one_for_received_letters(): void
    {
        [$a, $b] = $this->connectedPair();
        foreach (range(1, 12) as $i) {
            $this->letterBetween($b, $a, ['title' => 'Incoming '.$i]);
        }

        DB::enableQueryLog();
        $this->actingAs($a)->get('/letters')->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(15, $queries, "Expected bounded queries, got {$queries}");
    }
}
