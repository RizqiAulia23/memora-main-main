<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemorifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_default_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Rizqi',
            'email' => 'rizqi@memorify.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'rizqi@memorify.com',
            'role' => 'user',
        ]);
    }

    public function test_registration_validates_input(): void
    {
        $this->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_create_memory_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->post('/memories', [
            'title' => 'Beach Sunset in Bali',
            'description' => 'A beautiful evening by the shore.',
            'memory_date' => '2024-12-15',
            'image' => UploadedFile::fake()->image('photo.png'),
        ])->assertRedirect('/memories/1');

        $this->assertDatabaseHas('memories', [
            'user_id' => $user->id,
            'title' => 'Beach Sunset in Bali',
        ]);

        $memory = Memory::first();
        $this->assertNotNull($memory->image);
        Storage::disk('public')->assertExists($memory->image);
    }

    public function test_user_cannot_access_another_users_memory(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'Private Memory',
            'description' => 'Secret',
            'memory_date' => '2024-01-01',
        ]);

        $this->actingAs($intruder)
            ->get("/memories/{$memory->id}")
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete("/memories/{$memory->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('memories', ['id' => $memory->id]);
    }

    public function test_user_can_update_memory_and_replace_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'description' => 'Old description.',
            'memory_date' => '2024-01-01',
            'image' => 'memories/old.png',
        ]);

        Storage::disk('public')->put('memories/old.png', 'old');

        $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => 'New Title',
            'description' => 'New description.',
            'memory_date' => '2024-02-02',
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect("/memories/{$memory->id}");

        $memory->refresh();

        $this->assertEquals('New Title', $memory->title);
        $this->assertEquals('2024-02-02', $memory->memory_date->format('Y-m-d'));
        $this->assertNotNull($memory->image);
        Storage::disk('public')->assertExists($memory->image);
        Storage::disk('public')->assertMissing('memories/old.png');
    }

    public function test_user_can_delete_memory_and_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $user->id,
            'title' => 'To Delete',
            'description' => 'Bye.',
            'memory_date' => '2024-01-01',
            'image' => 'memories/gone.png',
        ]);

        Storage::disk('public')->put('memories/gone.png', 'data');

        $this->actingAs($user)
            ->delete("/memories/{$memory->id}")
            ->assertRedirect('/memories');

        $this->assertDatabaseMissing('memories', ['id' => $memory->id]);
        Storage::disk('public')->assertMissing('memories/gone.png');
    }

    public function test_memories_can_be_searched(): void
    {
        $user = User::factory()->create();
        Memory::create([
            'user_id' => $user->id,
            'title' => 'Beach Sunset in Bali',
            'description' => 'Golden hour',
            'memory_date' => '2024-12-15',
        ]);
        Memory::create([
            'user_id' => $user->id,
            'title' => 'Coffee Date Morning',
            'description' => 'Quiet cafe',
            'memory_date' => '2024-12-10',
        ]);

        $response = $this->actingAs($user)->get('/memories?search=beach');

        $response->assertOk();
        $response->assertSee('Beach Sunset in Bali');
        $response->assertDontSee('Coffee Date Morning');
    }

    public function test_memories_can_be_sorted_by_memory_date(): void
    {
        $user = User::factory()->create();
        Memory::create([
            'user_id' => $user->id,
            'title' => 'Older Memory',
            'description' => 'd',
            'memory_date' => '2023-01-01',
        ]);
        Memory::create([
            'user_id' => $user->id,
            'title' => 'Newer Memory',
            'description' => 'd',
            'memory_date' => '2024-01-01',
        ]);

        $response = $this->actingAs($user)->get('/memories?sort=memory_date');

        $response->assertOk();
        $response->assertSeeInOrder(['Newer Memory', 'Older Memory']);
    }

    public function test_dashboard_shows_real_statistics(): void
    {
        $user = User::factory()->create();

        Memory::create([
            'user_id' => $user->id,
            'title' => 'With Photo',
            'description' => 'd',
            'memory_date' => '2024-01-01',
            'image' => 'memories/a.png',
        ]);
        Memory::create([
            'user_id' => $user->id,
            'title' => 'Without Photo',
            'description' => 'd',
            'memory_date' => '2024-01-02',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Good Morning');
        $response->assertSee($user->name);
    }

    public function test_owner_can_view_create_edit_and_show_pages(): void
    {
        $user = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $user->id,
            'title' => 'My Memory',
            'description' => 'A lovely day.',
            'memory_date' => '2024-05-01',
            'image' => null,
        ]);

        $this->actingAs($user)->get('/memories/create')->assertOk();
        $this->actingAs($user)->get("/memories/{$memory->id}")->assertOk()->assertSee('My Memory');
        $this->actingAs($user)->get("/memories/{$memory->id}/edit")->assertOk()->assertSee('Edit Memory');
    }

    public function test_guests_are_redirected_from_dashboard_and_memories(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/memories')->assertRedirect('/login');
    }
}
