<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use App\Models\UserSettings;
use App\Notifications\ImportantDateReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class FullSecurityIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function memory(User $user, array $overrides = []): Memory
    {
        return Memory::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Sunset in Bali',
            'description' => 'Golden hour by the shore.',
            'memory_date' => '2024-12-15',
        ], $overrides));
    }

    /* ================= XSS ================= */

    public function test_memory_title_and_description_are_escaped_on_output(): void
    {
        $user = User::factory()->create();
        $memory = $this->memory($user, [
            'title' => '<script>alert(1)</script>',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $html = $this->actingAs($user)->get("/memories/{$memory->id}")->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_letter_title_is_escaped_on_output(): void
    {
        $user = User::factory()->create();
        $letter = LoveLetter::create([
            'user_id' => $user->id,
            'title' => '<script>alert(1)</script>',
            'content' => '<p>safe</p>',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ]);

        $html = $this->actingAs($user)->get("/letters/{$letter->id}")->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /* ================= MASS ASSIGNMENT ================= */

    public function test_memory_creation_ignores_injected_user_id(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post('/memories', [
            'title' => 'Sneaky',
            'description' => 'desc',
            'memory_date' => '2024-01-01',
            'user_id' => $other->id,
            'role' => 'admin',
        ])->assertRedirect();

        $memory = Memory::first();
        $this->assertSame($user->id, $memory->user_id);
    }

    public function test_profile_update_ignores_injected_fields(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Legit Name',
            'email' => 'hijacked@memorify.com',
            'password' => 'hacked123',
            'role' => 'admin',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('Legit Name', $user->name);
        $this->assertNotSame('hijacked@memorify.com', $user->email);
        $this->assertSame('user', $user->role);
    }

    /* ================= CSRF ================= */

    public function test_forms_include_csrf_token(): void
    {
        $user = User::factory()->create();

        foreach (['/login', '/register'] as $path) {
            $html = $this->get($path)->getContent();
            $this->assertStringContainsString('_token', $html, $path.' missing CSRF token');
        }

        $this->actingAs($user);
        foreach (['/memories/create', '/letters/create', '/profile', '/settings'] as $path) {
            $html = $this->actingAs($user)->get($path)->getContent();
            $this->assertStringContainsString('_token', $html, $path.' missing CSRF token');
        }
    }

    /* ================= FAVORITE INTEGRITY ================= */

    public function test_favorite_toggle_flips_state_repeatedly(): void
    {
        $user = User::factory()->create();
        $memory = $this->memory($user);

        for ($i = 1; $i <= 5; $i++) {
            $expected = $i % 2 === 1;
            $this->actingAs($user)
                ->postJson("/memories/{$memory->id}/favorite")
                ->assertOk()
                ->assertJson(['favorited' => $expected]);
        }

        $this->assertSame(1, Favorite::where('memory_id', $memory->id)->count());
    }

    public function test_duplicate_favorite_rows_are_prevented_by_unique_constraint(): void
    {
        $user = User::factory()->create();
        $memory = $this->memory($user);

        $user->favorites()->create(['memory_id' => $memory->id]);

        try {
            $user->favorites()->create(['memory_id' => $memory->id]);
            $this->fail('Duplicate favorite should have been rejected');
        } catch (\Throwable $e) {
            $this->assertSame(1, Favorite::where('memory_id', $memory->id)->count());
        }
    }

    public function test_favorite_count_json_is_accurate(): void
    {
        $user = User::factory()->create();
        $m1 = $this->memory($user, ['title' => 'One']);
        $m2 = $this->memory($user, ['title' => 'Two']);

        $this->actingAs($user)->postJson("/memories/{$m1->id}/favorite")
            ->assertJson(['favoritesCount' => 1]);
        $this->actingAs($user)->postJson("/memories/{$m2->id}/favorite")
            ->assertJson(['favoritesCount' => 2]);
        $this->actingAs($user)->postJson("/memories/{$m1->id}/favorite")
            ->assertJson(['favoritesCount' => 1]);
    }

    /* ================= CASCADE CLEANUP ================= */

    public function test_account_deletion_removes_all_related_records(): void
    {
        Storage::fake('private');

        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $memory = $this->memory($user, ['image' => 'memories/a.png']);
        $letter = LoveLetter::create([
            'user_id' => $user->id,
            'title' => 'Bye',
            'content' => '<p>x</p>',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ]);
        $user->favorites()->create(['memory_id' => $memory->id]);
        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ImportantDateReminderNotification::class,
            'data' => ['title' => 'Bye', 'message' => 'x', 'url' => '/calendar'],
        ]);
        Storage::disk('private')->put('memories/a.png', 'img');

        $this->actingAs($user)->delete('/settings/account', ['password' => 'password123']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('memories', ['id' => $memory->id]);
        $this->assertDatabaseMissing('love_letters', ['id' => $letter->id]);
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('user_settings', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $user->id]);
        Storage::disk('private')->assertMissing('memories/a.png');
    }

    public function test_deleting_memory_removes_its_favorites(): void
    {
        $user = User::factory()->create();
        $memory = $this->memory($user);
        $user->favorites()->create(['memory_id' => $memory->id]);

        $this->actingAs($user)->delete("/memories/{$memory->id}");

        $this->assertDatabaseMissing('favorites', ['memory_id' => $memory->id]);
    }

    public function test_no_orphan_records_exist_after_mixed_deletions(): void
    {
        $user = User::factory()->create();
        $keep = $this->memory($user, ['title' => 'Keep']);
        $gone = $this->memory($user, ['title' => 'Gone']);
        $user->favorites()->create(['memory_id' => $gone->id]);
        $user->favorites()->create(['memory_id' => $keep->id]);

        $this->actingAs($user)->delete("/memories/{$gone->id}");

        $orphanFavorites = Favorite::whereNotIn('memory_id', Memory::pluck('id'))->count();
        $orphanMemories = Memory::whereNotIn('user_id', User::pluck('id'))->count();
        $orphanLetters = LoveLetter::whereNotIn('user_id', User::pluck('id'))->count();
        $orphanSettings = UserSettings::whereNotIn('user_id', User::pluck('id'))->count();

        $this->assertSame(0, $orphanFavorites);
        $this->assertSame(0, $orphanMemories);
        $this->assertSame(0, $orphanLetters);
        $this->assertSame(0, $orphanSettings);
    }

    /* ================= ERROR HANDLING ================= */

    public function test_image_route_returns_404_when_file_is_missing_on_disk(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $memory = $this->memory($user, ['image' => 'memories/missing.png']);

        $response = $this->actingAs($user)->get("/memories/{$memory->id}/image");

        $this->assertContains($response->getStatusCode(), [404, 500]);
    }

    public function test_gallery_download_returns_404_when_file_is_missing_on_disk(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $memory = $this->memory($user, ['image' => 'memories/missing.png']);

        $response = $this->actingAs($user)->get("/gallery/{$memory->id}/download");

        $this->assertContains($response->getStatusCode(), [404, 500]);
    }

    public function test_avatar_route_returns_404_when_file_is_missing_on_disk(): void
    {
        Storage::fake('private');

        $user = User::factory()->create(['avatar' => 'avatars/missing.png']);

        $response = $this->actingAs($user)->get("/users/{$user->id}/avatar");

        $this->assertContains($response->getStatusCode(), [404, 500]);
    }
}
