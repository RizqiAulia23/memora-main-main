<?php

namespace Tests\Feature;

use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function createMemory(User $user, array $overrides = []): Memory
    {
        return Memory::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Sunset in Bali',
            'description' => 'Golden hour by the shore.',
            'memory_date' => '2024-12-15',
        ], $overrides));
    }

    private function createLetter(User $user, array $overrides = []): LoveLetter
    {
        return LoveLetter::create(array_merge([
            'user_id' => $user->id,
            'title' => 'To my dearest',
            'content' => '<p>You make my heart sing.</p>',
            'mood' => 'love',
            'letter_date' => '2024-12-15',
            'is_pinned' => false,
        ], $overrides));
    }

    public function test_user_can_favorite_and_unfavorite_a_memory_via_ajax(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user);

        $response = $this->actingAs($user)
            ->postJson("/memories/{$memory->id}/favorite");

        $response->assertOk()->assertJson(['favorited' => true]);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'memory_id' => $memory->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/memories/{$memory->id}/favorite");

        $response->assertOk()->assertJson(['favorited' => false]);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'memory_id' => $memory->id,
        ]);
    }

    public function test_favorites_page_lists_favorited_memories(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user);
        $other = $this->createMemory($user, ['title' => 'Not favorited']);

        $user->favorites()->create(['memory_id' => $memory->id]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertOk();
        $response->assertSee('Sunset in Bali');
        $response->assertDontSee('Not favorited');
    }

    public function test_memories_index_can_filter_by_favorites(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user);
        $other = $this->createMemory($user, ['title' => 'Not favorited']);

        $user->favorites()->create(['memory_id' => $memory->id]);

        $response = $this->actingAs($user)->get('/memories?favorites=1');

        $response->assertOk();
        $response->assertSee('Sunset in Bali');
        $response->assertDontSee('Not favorited');
    }

    public function test_gallery_shows_only_memories_with_photos(): void
    {
        $user = User::factory()->create();
        $withPhoto = $this->createMemory($user, ['image' => 'memories/a.png']);
        $this->createMemory($user, ['title' => 'No photo here']);

        $response = $this->actingAs($user)->get('/gallery');

        $response->assertOk();
        $response->assertSee('Sunset in Bali');
        $response->assertDontSee('No photo here');
    }

    public function test_user_can_download_a_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $memory = $this->createMemory($user, ['image' => 'memories/photo.png']);
        Storage::disk('public')->put('memories/photo.png', 'img');

        $response = $this->actingAs($user)->get("/gallery/{$memory->id}/download");

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_user_cannot_download_another_users_photo(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $memory = $this->createMemory($owner, ['image' => 'memories/photo.png']);

        $this->actingAs($intruder)
            ->get("/gallery/{$memory->id}/download")
            ->assertForbidden();
    }

    public function test_user_can_create_love_letter(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/letters', [
            'title' => 'My first letter',
            'content' => '<p>I love you endlessly.</p><script>alert(1)</script>',
            'mood' => 'love',
            'letter_date' => '2024-12-15',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect('/letters/1');
        $this->assertDatabaseHas('love_letters', [
            'user_id' => $user->id,
            'title' => 'My first letter',
            'mood' => 'love',
            'is_pinned' => true,
        ]);

        $letter = LoveLetter::first();
        $this->assertStringNotContainsString('script', $letter->content);
    }

    public function test_love_letter_validates_mood(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/letters', [
            'title' => 'Bad mood',
            'content' => 'x',
            'mood' => 'angry',
            'letter_date' => '2024-12-15',
        ])->assertSessionHasErrors('mood');
    }

    public function test_user_can_view_update_pin_and_delete_letter(): void
    {
        $user = User::factory()->create();
        $letter = $this->createLetter($user);

        $this->actingAs($user)->get("/letters/{$letter->id}")->assertOk()->assertSee('To my dearest');

        $this->actingAs($user)->patch("/letters/{$letter->id}", [
            'title' => 'Updated title',
            'content' => '<p>New words.</p>',
            'mood' => 'grateful',
            'letter_date' => '2024-12-16',
            'is_pinned' => '0',
        ])->assertRedirect("/letters/{$letter->id}");

        $this->assertDatabaseHas('love_letters', ['id' => $letter->id, 'title' => 'Updated title', 'mood' => 'grateful']);

        $this->actingAs($user)->post("/letters/{$letter->id}/pin");

        $this->assertDatabaseHas('love_letters', ['id' => $letter->id, 'is_pinned' => true]);

        $this->actingAs($user)->delete("/letters/{$letter->id}")->assertRedirect('/letters');

        $this->assertDatabaseMissing('love_letters', ['id' => $letter->id]);
    }

    public function test_user_cannot_access_another_users_letter(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $letter = $this->createLetter($owner);

        $this->actingAs($intruder)->get("/letters/{$letter->id}")->assertForbidden();
        $this->actingAs($intruder)->patch("/letters/{$letter->id}", [
            'title' => 'Hacked',
            'content' => 'x',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertForbidden();
        $this->actingAs($intruder)->delete("/letters/{$letter->id}")->assertForbidden();
        $this->actingAs($intruder)->post("/letters/{$letter->id}/pin")->assertForbidden();
    }

    public function test_pinned_letters_are_sorted_first(): void
    {
        $user = User::factory()->create();
        $pinned = $this->createLetter($user, ['title' => 'Pinned one', 'is_pinned' => true]);
        $normal = $this->createLetter($user, ['title' => 'Normal one']);

        $response = $this->actingAs($user)->get('/letters');

        $response->assertOk();
        $response->assertSeeInOrder(['Pinned one', 'Normal one']);
    }

    public function test_timeline_groups_memories_by_month(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user, ['title' => 'Winter Memory', 'memory_date' => '2024-01-10']);
        $this->createMemory($user, ['title' => 'Summer Memory', 'memory_date' => '2024-07-04']);

        $response = $this->actingAs($user)->get('/timeline?year=2024');

        $response->assertOk();
        $response->assertSee('January');
        $response->assertSee('July');
        $response->assertSee('Winter Memory');
        $response->assertSee('Summer Memory');
    }

    public function test_calendar_shows_month_and_day_memories(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user, ['memory_date' => now()->startOfMonth()->addDay(5)->format('Y-m-d')]);

        $month = now()->format('Y-m');
        $response = $this->actingAs($user)->get('/calendar?month='.$month);

        $response->assertOk();
        $response->assertSee(now()->format('F Y'));
    }

    public function test_calendar_date_endpoint_returns_memories(): void
    {
        $user = User::factory()->create();
        $date = now()->startOfMonth()->addDay(5);
        $memory = $this->createMemory($user, ['memory_date' => $date->format('Y-m-d')]);

        $response = $this->actingAs($user)
            ->getJson('/calendar/date?date='.$date->format('Y-m-d'));

        $response->assertOk();
        $response->assertJsonFragment(['date' => $date->format('Y-m-d')]);
        $response->assertJsonStructure(['html']);
    }

    public function test_global_search_finds_memories_and_letters(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user, ['title' => 'Coffee Date Morning']);
        $this->createLetter($user, ['title' => 'A letter about coffee']);

        $response = $this->actingAs($user)->get('/search?q=coffee');

        $response->assertOk();
        $response->assertSee('Coffee Date Morning');
        $response->assertSee('A letter about coffee');
    }

    public function test_user_can_update_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Riz & Alex',
            'bio' => 'Forever together.',
            'partner_name' => 'Alex',
            'relationship_date' => '2022-06-01',
            'location' => 'Paris, France',
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Riz & Alex',
            'bio' => 'Forever together.',
            'partner_name' => 'Alex',
            'location' => 'Paris, France',
        ]);
        $this->assertNotNull($user->fresh()->avatar);
    }

    public function test_user_can_remove_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'avatars/old.png']);
        Storage::disk('public')->put('avatars/old.png', 'img');

        $this->actingAs($user)->delete('/profile/avatar')->assertRedirect();

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing('avatars/old.png');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('currentpass')]);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'currentpass',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect();

        $this->assertTrue(password_verify('newpass123', $user->fresh()->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('currentpass')]);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_user_can_update_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', [
            'theme' => 'dark',
            'notifications_enabled' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'theme' => 'dark',
            'notifications_enabled' => true,
        ]);
    }

    public function test_settings_rejects_invalid_theme(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', [
            'theme' => 'neon',
        ])->assertSessionHasErrors('theme');
    }

    public function test_user_can_delete_account_with_password(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $memory = $this->createMemory($user, ['image' => 'memories/photo.png']);
        Storage::disk('public')->put('memories/photo.png', 'img');

        $response = $this->actingAs($user)->delete('/settings/account', [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('memories', ['id' => $memory->id]);
        Storage::disk('public')->assertMissing('memories/photo.png');
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->actingAs($user)->delete('/settings/account', [
            'password' => 'wrong',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_dashboard_shows_favorites_letters_and_anniversary(): void
    {
        $user = User::factory()->create(['relationship_date' => '2022-06-01']);
        $memory = $this->createMemory($user);
        $user->favorites()->create(['memory_id' => $memory->id]);
        $this->createLetter($user);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Favorite Memories');
        $response->assertSee('Love Letters');
    }

    public function test_all_new_pages_require_auth(): void
    {
        foreach (['/favorites', '/gallery', '/letters', '/timeline', '/calendar', '/search', '/profile', '/settings'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }
}
