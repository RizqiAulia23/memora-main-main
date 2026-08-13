<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\ImageStore;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class FileCleanupFailureTest extends TestCase
{
    use RefreshDatabase;

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = tempnam(sys_get_temp_dir(), 'memorify_log_');

        config([
            'logging.default' => 'memorify_test',
            'logging.channels.memorify_test' => [
                'driver' => 'single',
                'path' => $this->logPath,
                'level' => 'debug',
            ],
        ]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->logPath)) {
            @unlink($this->logPath);
        }

        parent::tearDown();
    }

    private function failingDisk(array $deleteResults): Filesystem
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('delete')->andReturn(...$deleteResults);

        app('filesystem')->set('private', $disk);

        return $disk;
    }

    private function assertLogContains(string $needle): void
    {
        $this->assertStringContainsString($needle, (string) file_get_contents($this->logPath));
    }

    private function createMemory(User $user, array $overrides = []): Memory
    {
        return Memory::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Sunset in Bali',
            'description' => 'Golden hour by the shore.',
            'memory_date' => '2024-12-15',
        ], $overrides));
    }

    public function test_image_store_delete_returns_false_and_logs_warning_when_both_attempts_fail(): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('delete')->twice()->andReturn(false, false);
        app('filesystem')->set('private', $disk);

        $store = new ImageStore('memories');

        $this->assertFalse($store->delete('memories/lost.png', 'test-flow'));
        $this->assertLogContains('File deletion failed after retry');
        $this->assertLogContains('memories/lost.png');
        $this->assertLogContains('test-flow');
    }

    public function test_image_store_delete_returns_true_after_successful_retry(): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('delete')->twice()->andReturn(false, true);
        app('filesystem')->set('private', $disk);

        $store = new ImageStore('memories');

        $this->assertTrue($store->delete('memories/recovered.png'));
        $this->assertStringNotContainsString('File deletion failed after retry', (string) file_get_contents($this->logPath));
    }

    public function test_image_store_delete_returns_true_for_null_or_empty_path(): void
    {
        $store = new ImageStore('memories');

        $this->assertTrue($store->delete(null));
        $this->assertStringNotContainsString('File deletion failed after retry', (string) file_get_contents($this->logPath));
    }

    public function test_memory_destroy_keeps_redirect_success_and_logs_warning_when_file_delete_fails(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user, ['image' => 'memories/gone.png']);

        $this->failingDisk([false, false]);

        $this->actingAs($user)
            ->delete("/memories/{$memory->id}")
            ->assertRedirect(route('memories.index'))
            ->assertSessionHas('success', 'Memory deleted successfully.');

        $this->assertDatabaseMissing('memories', ['id' => $memory->id]);
        $this->assertLogContains('File deletion failed after retry');
        $this->assertLogContains('memories/gone.png');
        $this->assertLogContains('memory-destroy');
    }

    public function test_memory_destroy_keeps_file_when_db_delete_fails(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user, ['image' => 'memories/kept.png']);

        Memory::deleting(function () {
            throw new \RuntimeException('delete failed');
        });

        $this->actingAs($user)
            ->delete("/memories/{$memory->id}")
            ->assertStatus(500);

        $this->assertDatabaseHas('memories', ['id' => $memory->id, 'image' => 'memories/kept.png']);
        $this->assertStringNotContainsString('File deletion failed after retry', (string) file_get_contents($this->logPath));
    }

    public function test_remove_avatar_sets_null_and_logs_warning_when_file_delete_fails(): void
    {
        $user = User::factory()->create(['avatar' => 'avatars/me.png']);

        $this->failingDisk([false, false]);

        $this->actingAs($user)
            ->delete(route('profile.remove-avatar'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Avatar removed.');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'avatar' => null]);
        $this->assertLogContains('File deletion failed after retry');
        $this->assertLogContains('avatars/me.png');
        $this->assertLogContains('profile-avatar-removal');
    }

    public function test_account_deletion_logs_error_for_failed_file_and_still_processes_remaining_files(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $this->createMemory($user, ['image' => 'memories/a.png', 'memory_date' => '2024-01-01']);
        $this->createMemory($user, ['image' => 'memories/b.png', 'memory_date' => '2024-02-02']);
        $user->update(['avatar' => 'avatars/me.png']);

        $disk = $this->failingDisk([true, false, false, true]);

        $this->actingAs($user)
            ->delete(route('settings.delete-account'), ['password' => 'password123'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Your account has been deleted. Goodbye.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('memories', ['user_id' => $user->id]);

        $disk->shouldHaveReceived('delete')->with('memories/a.png')->once();
        $disk->shouldHaveReceived('delete')->with('memories/b.png')->twice();
        $disk->shouldHaveReceived('delete')->with('avatars/me.png')->once();

        $this->assertLogContains('Failed to delete memory image during account deletion');
        $this->assertLogContains('memories/b.png');
        $this->assertLogContains('File deletion failed after retry');
    }

    public function test_memory_image_replacement_cleans_new_image_and_preserves_db_exception_when_db_update_fails(): void
    {
        $user = User::factory()->create();
        $memory = $this->createMemory($user, [
            'title' => 'Old Title',
            'image' => 'memories/old.png',
        ]);

        Memory::updating(function () {
            throw new \RuntimeException('saving failed');
        });

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFileAs')->andReturn('memories/new.png');
        $disk->shouldReceive('delete')->twice()->andReturn(false, false);
        app('filesystem')->set('private', $disk);

        $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => 'New Title',
            'description' => 'New description.',
            'memory_date' => '2024-02-02',
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertStatus(500);

        $this->assertDatabaseHas('memories', [
            'id' => $memory->id,
            'title' => 'Old Title',
            'image' => 'memories/old.png',
        ]);

        $disk->shouldHaveReceived('delete')->with('memories/new.png')->twice();
        $this->assertLogContains('File deletion failed after retry');
        $this->assertLogContains('memories/new.png');
        $this->assertLogContains('memory-update-cleanup');
    }

    public function test_account_deletion_keeps_files_when_db_delete_fails(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $memory = $this->createMemory($user, ['image' => 'memories/account.png']);
        $user->update(['avatar' => 'avatars/me.png']);

        User::deleting(function () {
            throw new \RuntimeException('delete failed');
        });

        $this->actingAs($user)
            ->delete(route('settings.delete-account'), ['password' => 'password123'])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('memories', ['id' => $memory->id, 'image' => 'memories/account.png']);
        $this->assertStringNotContainsString('File deletion failed after retry', (string) file_get_contents($this->logPath));
    }
}
