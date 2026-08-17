<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use App\Policies\ConnectionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConnectionTest extends TestCase
{
    use RefreshDatabase;

    // ---------- CONNECTION CODE GENERATION ----------

    public function test_every_user_gets_a_connection_code_when_created(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->connection_code);
        $this->assertSame($user->connection_code, $user->fresh()->connection_code);
    }

    public function test_registration_assigns_a_connection_code(): void
    {
        $this->post('/register', [
            'name' => 'New Member',
            'email' => 'newmember@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'newmember@example.com']);
        $this->assertMatchesRegularExpression(
            '/^\d{8}$/',
            User::query()->where('email', 'newmember@example.com')->value('connection_code')
        );
    }

    public function test_connection_code_is_exactly_eight_digits(): void
    {
        $code = User::factory()->create()->connection_code;

        $this->assertMatchesRegularExpression('/^\d{8}$/', $code);
        $this->assertSame(8, strlen($code));
    }

    public function test_connection_code_is_numeric_only(): void
    {
        $this->assertTrue(ctype_digit(User::factory()->create()->connection_code));
    }

    public function test_connection_codes_are_unique(): void
    {
        User::factory()->count(50)->create();

        $codes = User::query()->pluck('connection_code');
        $this->assertSame($codes->count(), $codes->unique()->count());
    }

    public function test_connection_code_is_not_based_on_user_id(): void
    {
        $user = User::factory()->create();

        $this->assertNotSame((string) $user->id, $user->connection_code);
        $this->assertNotSame(str_pad((string) $user->id, 8, '0', STR_PAD_LEFT), $user->connection_code);
    }

    public function test_connection_code_is_stable_across_reloads(): void
    {
        $user = User::factory()->create();
        $code = $user->connection_code;

        $this->assertSame($code, User::query()->find($user->id)->connection_code);
        $this->assertSame($code, $user->fresh()->connection_code);
    }

    public function test_profile_update_does_not_change_connection_code(): void
    {
        $user = User::factory()->create();
        $code = $user->connection_code;

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Updated Name', 'bio' => 'Hello there'])
            ->assertRedirect();

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame($code, $user->fresh()->connection_code);
    }

    public function test_existing_users_get_connection_code_via_backfill(): void
    {
        Schema::table('users', function ($table) {
            $table->string('connection_code', 8)->nullable()->change();
        });

        $id = DB::table('users')->insertGetId([
            'name' => 'Legacy User',
            'email' => 'legacy@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull(DB::table('users')->where('id', $id)->value('connection_code'));

        $migration = require database_path('migrations/2026_08_14_000002_backfill_connection_codes_on_users_table.php');
        $migration->up();

        $this->assertMatchesRegularExpression(
            '/^\d{8}$/',
            DB::table('users')->where('id', $id)->value('connection_code')
        );
    }

    public function test_backfill_does_not_overwrite_existing_codes(): void
    {
        $user = User::factory()->create();
        $code = $user->connection_code;

        $migration = require database_path('migrations/2026_08_14_000002_backfill_connection_codes_on_users_table.php');
        $migration->up();

        $this->assertSame($code, $user->fresh()->connection_code);
    }

    // ---------- SEND ----------

    public function test_user_can_send_connection_request_with_code(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('connections', [
            'sender_id' => $a->id,
            'receiver_id' => $b->id,
            'status' => Connection::PENDING,
        ]);
    }

    public function test_invalid_connection_code_is_rejected(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => '99999999'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('connections', 0);
    }

    public function test_malformed_connection_code_is_rejected_by_validation(): void
    {
        $a = User::factory()->create();

        foreach (['', '123', 'abcdefgh', '123456789', '1234 5678'] as $bad) {
            $this->actingAs($a)
                ->post('/connections', ['connection_code' => $bad])
                ->assertSessionHasErrors('connection_code');
        }

        $this->assertDatabaseCount('connections', 0);
    }

    public function test_user_cannot_connect_with_own_code(): void
    {
        $a = User::factory()->create();

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $a->connection_code])
            ->assertForbidden();

        $this->assertDatabaseCount('connections', 0);
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('connections', 1);
    }

    public function test_duplicate_accepted_request_is_rejected(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('connections', 1);
    }

    public function test_reverse_pending_request_is_rejected(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->pending()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('connections', 1);
        $this->assertDatabaseHas('connections', [
            'sender_id' => $b->id,
            'receiver_id' => $a->id,
            'status' => Connection::PENDING,
        ]);
    }

    public function test_reverse_accepted_request_is_rejected(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('connections', 1);
    }

    public function test_rejected_request_can_be_reactivated(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('connections', [
            'id' => $connection->id,
            'status' => Connection::PENDING,
        ]);
    }

    public function test_reactivation_uses_the_same_row(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)->post('/connections', ['connection_code' => $b->connection_code]);

        $this->assertSame($connection->id, $connection->fresh()->id);
        $this->assertSame($a->id, $connection->fresh()->sender_id);
        $this->assertSame($b->id, $connection->fresh()->receiver_id);
        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_reactivation_does_not_create_duplicate_row(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)->post('/connections', ['connection_code' => $b->connection_code]);

        $this->assertDatabaseCount('connections', 1);
    }

    public function test_sender_id_never_comes_from_request_body(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $evil = User::factory()->create();

        $this->actingAs($a)
            ->post('/connections', [
                'connection_code' => $b->connection_code,
                'sender_id' => $evil->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('connections', [
            'sender_id' => $a->id,
            'receiver_id' => $b->id,
        ]);
        $this->assertDatabaseMissing('connections', ['sender_id' => $evil->id]);
    }

    public function test_receiver_id_never_comes_from_request_body(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $evil = User::factory()->create();

        $this->actingAs($a)
            ->post('/connections', [
                'connection_code' => $b->connection_code,
                'receiver_id' => $evil->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('connections', [
            'sender_id' => $a->id,
            'receiver_id' => $b->id,
        ]);
        $this->assertDatabaseMissing('connections', ['receiver_id' => $evil->id]);
    }

    // ---------- ACCEPT ----------

    public function test_receiver_can_accept_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->patch("/connections/{$connection->id}/accept")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Connection::ACCEPTED, $connection->fresh()->status);
    }

    public function test_sender_cannot_accept_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->patch("/connections/{$connection->id}/accept")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_other_user_cannot_accept_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->patch("/connections/{$connection->id}/accept")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_only_pending_request_can_be_accepted(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->patch("/connections/{$connection->id}/accept")
            ->assertForbidden();

        $this->assertSame(Connection::ACCEPTED, $connection->fresh()->status);
    }

    // ---------- REJECT ----------

    public function test_receiver_can_reject_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->patch("/connections/{$connection->id}/reject")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Connection::REJECTED, $connection->fresh()->status);
    }

    public function test_sender_cannot_reject_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->patch("/connections/{$connection->id}/reject")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_other_user_cannot_reject_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->patch("/connections/{$connection->id}/reject")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_only_pending_request_can_be_rejected(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->patch("/connections/{$connection->id}/reject")
            ->assertForbidden();

        $this->assertSame(Connection::REJECTED, $connection->fresh()->status);
    }

    // ---------- CANCEL ----------

    public function test_sender_can_cancel_pending_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->delete("/connections/{$connection->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('connections', ['id' => $connection->id]);
    }

    public function test_receiver_cannot_cancel_pending_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    public function test_other_user_cannot_cancel_pending_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    public function test_accepted_connection_cannot_be_cancelled(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->assertFalse((new ConnectionPolicy)->cancel($a, $connection));
        $this->assertFalse((new ConnectionPolicy)->cancel($b, $connection));
        $this->assertSame(Connection::ACCEPTED, $connection->fresh()->status);
    }

    // ---------- DISCONNECT ----------

    public function test_sender_can_disconnect_accepted_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)
            ->delete("/connections/{$connection->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('connections', ['id' => $connection->id]);
    }

    public function test_receiver_can_disconnect_accepted_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($b)
            ->delete("/connections/{$connection->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('connections', ['id' => $connection->id]);
    }

    public function test_other_user_cannot_disconnect_accepted_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    public function test_pending_request_cannot_use_disconnect(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->assertFalse((new ConnectionPolicy)->delete($b, $connection));

        $this->actingAs($b)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    public function test_rejected_connection_cannot_be_deleted(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $connection = Connection::factory()->rejected()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->assertFalse((new ConnectionPolicy)->delete($a, $connection));
        $this->assertFalse((new ConnectionPolicy)->delete($b, $connection));

        $this->actingAs($a)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    // ---------- LIST ----------

    public function test_connections_page_shows_my_connection_code(): void
    {
        $a = User::factory()->create();

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk();
        $response->assertSee('Your Connection Code');
        $response->assertSee('Enter Connection Code');
        $response->assertSee($a->connection_code);
        $response->assertSee('Copy');
        $response->assertSee('Connect');
    }

    public function test_user_only_sees_their_own_connections(): void
    {
        $a = User::factory()->create(['name' => 'User Alpha']);
        $b = User::factory()->create(['name' => 'User Bravo']);
        $c = User::factory()->create(['name' => 'User Charlie']);
        $d = User::factory()->create(['name' => 'User Delta']);

        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        Connection::factory()->accepted()->create(['sender_id' => $c->id, 'receiver_id' => $d->id]);

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk()->assertSee('User Bravo');
        $response->assertDontSee('User Charlie');
        $response->assertDontSee('User Delta');
    }

    public function test_incoming_requests_are_correct(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['name' => 'Incoming Bob']);
        $c = User::factory()->create(['name' => 'Outgoing Carol']);

        Connection::factory()->pending()->create(['sender_id' => $b->id, 'receiver_id' => $a->id]);
        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('incoming'));
        $this->assertSame($b->id, $response->viewData('incoming')->first()->sender_id);
        $response->assertSee('Incoming Bob');
    }

    public function test_outgoing_requests_are_correct(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['name' => 'Outgoing Bob']);
        $c = User::factory()->create(['name' => 'Incoming Carol']);

        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        Connection::factory()->pending()->create(['sender_id' => $c->id, 'receiver_id' => $a->id]);

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('outgoing'));
        $this->assertSame($b->id, $response->viewData('outgoing')->first()->receiver_id);
        $response->assertSee('Outgoing Bob');
    }

    public function test_accepted_connections_are_correct(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['name' => 'Connected Bob']);
        $c = User::factory()->create(['name' => 'Pending Carol']);

        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('connected'));
        $response->assertSee('Connected Bob');
    }

    public function test_connections_page_does_not_expose_sensitive_data(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['name' => 'Visible Partner']);
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $response = $this->actingAs($a)->get('/connections');

        $response->assertOk();
        $response->assertSee('Visible Partner');
        $response->assertDontSee($b->email);
        $response->assertDontSee($b->connection_code);
        $response->assertDontSee('remember_token');
        $response->assertDontSee('$2y$10');
    }

    public function test_connections_page_has_no_n_plus_one_queries(): void
    {
        $a = User::factory()->create();
        $others = User::factory()->count(6)->create();

        foreach ($others as $other) {
            Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $other->id]);
        }

        DB::enableQueryLog();
        $this->actingAs($a)->get('/connections');
        $queries = count(DB::getQueryLog());

        $this->assertLessThan(15, $queries);
    }

    // ---------- AUTH / IDOR ----------

    public function test_unauthenticated_user_cannot_access_connections_page(): void
    {
        $this->get('/connections')->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_send_connection_request(): void
    {
        $b = User::factory()->create();

        $this->post('/connections', ['connection_code' => $b->connection_code])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('connections', 0);
    }

    public function test_idor_user_cannot_view_others_connection(): void
    {
        $a = User::factory()->create();
        $c = User::factory()->create();
        $d = User::factory()->create();

        $connection = Connection::factory()->accepted()->create(['sender_id' => $c->id, 'receiver_id' => $d->id]);

        $this->assertFalse((new ConnectionPolicy)->view($a, $connection));

        $response = $this->actingAs($a)->get('/connections');
        $response->assertDontSee($c->name);
        $response->assertDontSee($d->name);
    }

    public function test_idor_user_cannot_accept_others_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->patch("/connections/{$connection->id}/accept")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_idor_user_cannot_reject_others_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->patch("/connections/{$connection->id}/reject")
            ->assertForbidden();

        $this->assertSame(Connection::PENDING, $connection->fresh()->status);
    }

    public function test_idor_user_cannot_cancel_others_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    public function test_idor_user_cannot_disconnect_others_connection(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $connection = Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($c)
            ->delete("/connections/{$connection->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('connections', ['id' => $connection->id]);
    }

    // ---------- THEME ----------

    public function test_connections_page_uses_light_theme(): void
    {
        $user = User::factory()->create();

        $user->settings()->create(['theme' => 'light', 'notifications_enabled' => true]);

        $this->actingAs($user)
            ->get('/connections')
            ->assertOk()
            ->assertSee('data-theme="light"', false);
    }

    public function test_connections_page_uses_dark_theme(): void
    {
        $user = User::factory()->create();

        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);

        $this->actingAs($user)
            ->get('/connections')
            ->assertOk()
            ->assertSee('data-theme="dark"', false);
    }

    public function test_connection_requests_are_rate_limited(): void
    {
        $a = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($a)->post('/connections', ['connection_code' => '00000000'])
                ->assertStatus(302);
        }

        $this->actingAs($a)->post('/connections', ['connection_code' => '00000000'])
            ->assertStatus(429);
    }

    public function test_connected_pair_shows_couple_tools(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['name' => 'Tool Partner']);
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        $this->actingAs($a)->get('/connections')
            ->assertOk()
            ->assertSee('conn-pair-tools', false)
            ->assertSee('Important Dates')
            ->assertSee('Playlist')
            ->assertSee('Bucket List')
            ->assertSee('Couple Timeline')
            ->assertSee(route('letters.create', ['receiver_id' => $b->id]), false);
    }

    // ---------- ACCOUNT CASCADE ----------

    public function test_deleting_user_cascades_their_connections(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $c = User::factory()->create();

        Connection::factory()->pending()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);
        Connection::factory()->accepted()->create(['sender_id' => $c->id, 'receiver_id' => $a->id]);
        Connection::factory()->accepted()->create(['sender_id' => $b->id, 'receiver_id' => $c->id]);

        $a->delete();

        $this->assertDatabaseMissing('connections', ['sender_id' => $a->id]);
        $this->assertDatabaseMissing('connections', ['receiver_id' => $a->id]);
        $this->assertDatabaseHas('connections', ['sender_id' => $b->id, 'receiver_id' => $c->id]);
    }
}
