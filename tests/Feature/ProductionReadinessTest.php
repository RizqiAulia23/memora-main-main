<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
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

    public function test_profile_page_does_not_nest_remove_avatar_form_inside_update_form(): void
    {
        $user = User::factory()->create(['avatar' => 'avatars/me.png']);

        $html = $this->actingAs($user)->get('/profile')->getContent();

        $avatarFormOpen = strpos($html, 'data-avatar-form');
        $this->assertNotFalse($avatarFormOpen);

        $formEnd = strpos($html, '</form>', $avatarFormOpen);
        $this->assertNotFalse($formEnd);

        $inner = substr($html, $avatarFormOpen, $formEnd - $avatarFormOpen);

        $this->assertStringNotContainsString('/profile/avatar', $inner);
        $this->assertStringContainsString('/profile/avatar', $html);
    }

    public function test_memory_image_replacement_keeps_old_file_when_db_update_fails(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $memory = $this->createMemory($user, [
            'title' => 'Old Title',
            'image' => 'memories/old.png',
        ]);
        Storage::disk('private')->put('memories/old.png', 'old data');

        Memory::updating(function () {
            throw new \RuntimeException('saving failed');
        });

        $response = $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => 'New Title',
            'description' => 'New description.',
            'memory_date' => '2024-02-02',
            'image' => UploadedFile::fake()->image('new.png'),
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseHas('memories', [
            'id' => $memory->id,
            'title' => 'Old Title',
            'image' => 'memories/old.png',
        ]);
        Storage::disk('private')->assertExists('memories/old.png');
        $this->assertCount(1, Storage::disk('private')->files('memories'));
    }

    public function test_memory_deletion_keeps_file_when_row_delete_fails(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $memory = $this->createMemory($user, ['image' => 'memories/gone.png']);
        Storage::disk('private')->put('memories/gone.png', 'data');

        Memory::deleting(function () {
            throw new \RuntimeException('delete failed');
        });

        $this->actingAs($user)
            ->delete("/memories/{$memory->id}")
            ->assertStatus(500);

        $this->assertDatabaseHas('memories', ['id' => $memory->id]);
        Storage::disk('private')->assertExists('memories/gone.png');
    }

    public function test_account_deletion_keeps_files_when_user_delete_fails(): void
    {
        Storage::fake('private');

        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $memory = $this->createMemory($user, ['image' => 'memories/account.png']);
        $user->update(['avatar' => 'avatars/me.png']);
        Storage::disk('private')->put('memories/account.png', 'data');
        Storage::disk('private')->put('avatars/me.png', 'data');

        User::deleting(function () {
            throw new \RuntimeException('delete failed');
        });

        $this->actingAs($user)
            ->delete('/settings/account', ['password' => 'password123'])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('memories', ['id' => $memory->id]);
        Storage::disk('private')->assertExists('memories/account.png');
        Storage::disk('private')->assertExists('avatars/me.png');
    }

    public function test_storage_usage_cache_is_flushed_when_memory_changes(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $this->createMemory($user, ['image' => 'memories/a.png']);
        Storage::disk('private')->put('memories/a.png', 'img');

        $this->actingAs($user)->get('/settings');
        $this->assertTrue(Cache::has('storage.usage.'.$user->id));

        $this->createMemory($user, [
            'title' => 'Fresh photo memory',
            'image' => 'memories/b.png',
            'memory_date' => '2024-06-01',
        ]);
        Storage::disk('private')->put('memories/b.png', 'img');

        $this->assertFalse(Cache::has('storage.usage.'.$user->id));
    }

    public function test_500_page_does_not_leak_exception_details_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        Route::get('tf/boom', fn () => throw new \RuntimeException('SECRET_MARKER_123'));

        $this->get('tf/boom')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertDontSee('SECRET_MARKER_123')
            ->assertDontSee('vendor/laravel/framework');
    }
}
