<?php

namespace Tests\Feature;

use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_private_memory_image_without_login(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'Secret photo',
            'description' => 'Private',
            'memory_date' => '2024-12-15',
            'image' => 'memories/photo.png',
        ]);
        Storage::disk('private')->put('memories/photo.png', 'img');

        $this->get("/memories/{$memory->id}/image")->assertRedirect('/login');
    }

    public function test_owner_can_access_private_memory_image(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'Our photo',
            'description' => 'Private',
            'memory_date' => '2024-12-15',
            'image' => 'memories/photo.png',
        ]);
        Storage::disk('private')->put('memories/photo.png', 'img');

        $this->actingAs($owner)->get("/memories/{$memory->id}/image")->assertOk();
    }

    public function test_another_user_cannot_access_private_memory_image(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'Private photo',
            'description' => 'Private',
            'memory_date' => '2024-12-15',
            'image' => 'memories/photo.png',
        ]);
        Storage::disk('private')->put('memories/photo.png', 'img');

        $this->actingAs($intruder)
            ->get("/memories/{$memory->id}/image")
            ->assertForbidden();
    }

    public function test_image_route_returns_404_when_owner_has_no_image(): void
    {
        $owner = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'No image',
            'description' => 'Text only',
            'memory_date' => '2024-12-15',
        ]);

        $this->actingAs($owner)->get("/memories/{$memory->id}/image")->assertNotFound();
    }

    public function test_unauthorized_user_cannot_access_private_avatar(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create(['avatar' => 'avatars/me.png']);
        $intruder = User::factory()->create();
        Storage::disk('private')->put('avatars/me.png', 'img');

        $this->actingAs($intruder)->get("/users/{$owner->id}/avatar")->assertForbidden();
    }

    public function test_owner_can_access_own_avatar(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create(['avatar' => 'avatars/me.png']);
        Storage::disk('private')->put('avatars/me.png', 'img');

        $this->actingAs($owner)->get("/users/{$owner->id}/avatar")->assertOk();
    }

    public function test_avatar_route_returns_404_when_user_has_no_avatar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get("/users/{$user->id}/avatar")->assertNotFound();
    }

    public function test_avatar_route_returns_404_for_unknown_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/users/999999/avatar')
            ->assertNotFound();
    }

    public function test_idor_does_not_leak_other_users_resources(): void
    {
        Storage::fake('private');

        $owner = User::factory()->create([
            'name' => 'Secret Owner',
            'avatar' => 'avatars/secret.png',
        ]);
        $intruder = User::factory()->create();

        $memory = Memory::create([
            'user_id' => $owner->id,
            'title' => 'Private Title',
            'description' => 'A very secret memory.',
            'memory_date' => '2024-12-15',
            'image' => 'memories/secret.png',
        ]);

        $letter = LoveLetter::create([
            'user_id' => $owner->id,
            'title' => 'Private Letter',
            'content' => '<p>Secret words.</p>',
            'mood' => 'love',
            'letter_date' => '2024-12-15',
            'is_pinned' => false,
        ]);

        Storage::disk('private')->put('memories/secret.png', 'img');
        Storage::disk('private')->put('avatars/secret.png', 'img');

        $this->actingAs($intruder)->get("/memories/{$memory->id}")->assertForbidden();
        $this->actingAs($intruder)->get("/memories/{$memory->id}/edit")->assertForbidden();
        $this->actingAs($intruder)->delete("/memories/{$memory->id}")->assertForbidden();
        $this->actingAs($intruder)->get("/memories/{$memory->id}/image")->assertForbidden();
        $this->actingAs($intruder)->post("/memories/{$memory->id}/favorite")->assertForbidden();
        $this->actingAs($intruder)->get("/gallery/{$memory->id}/download")->assertForbidden();

        $this->actingAs($intruder)->get("/letters/{$letter->id}")->assertForbidden();
        $this->actingAs($intruder)->patch("/letters/{$letter->id}", [
            'title' => 'Hacked',
            'content' => 'x',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertForbidden();
        $this->actingAs($intruder)->post("/letters/{$letter->id}/pin")->assertForbidden();

        $this->actingAs($intruder)->get("/users/{$owner->id}/avatar")->assertForbidden();
    }

    public function test_missing_resources_return_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/memories/999999')->assertNotFound();
        $this->actingAs($user)->get('/memories/999999/edit')->assertNotFound();
        $this->actingAs($user)->get('/letters/999999')->assertNotFound();
        $this->actingAs($user)->get('/gallery/999999/download')->assertNotFound();
    }

    public function test_instant_search_displays_only_own_memories(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Memory::create([
            'user_id' => $owner->id,
            'title' => 'Owner Coffee',
            'description' => 'Private',
            'memory_date' => '2024-12-15',
        ]);
        Memory::create([
            'user_id' => $other->id,
            'title' => 'Other Coffee',
            'description' => 'Should not leak',
            'memory_date' => '2024-12-15',
        ]);

        $response = $this->actingAs($owner)->get('/search/instant?q=coffee');

        $response->assertOk();
        $response->assertSee('Owner Coffee');
        $response->assertDontSee('Other Coffee');
    }

    public function test_profile_uploaded_avatar_is_stored_on_private_disk(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Riz & Alex',
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect();

        $path = $user->fresh()->avatar;
        $this->assertNotNull($path);
        Storage::disk('private')->assertExists($path);
        $this->assertStringStartsWith('avatars/', $path);
    }

    public function test_instant_search_endpoint_is_rate_limited(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        for ($i = 0; $i < 30; $i++) {
            $this->get('/search/instant?q=coffee');
        }

        $this->get('/search/instant?q=coffee')->assertTooManyRequests();
    }
}
