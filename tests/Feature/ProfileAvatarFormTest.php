<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression tests for BUG-002: the avatar form on /profile only submits the
 * avatar file (no `name` field), but UpdateProfileRequest required `name`, so
 * every avatar upload/replacement from the UI failed validation
 * ("The name field is required.") and users.avatar stayed NULL.
 *
 * Fixed by making `name` optional when `avatar` is present
 * (required_without:avatar) and keeping the current name in ProfileService
 * when it is not submitted.
 */
class ProfileAvatarFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_upload_without_name_works(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotNull($user->fresh()->avatar);
        Storage::disk('private')->assertExists($user->fresh()->avatar);
        $this->assertSame($user->fresh()->name, $user->name);
    }

    public function test_avatar_replacement_without_name_removes_previous_file(): void
    {
        Storage::fake('private');

        $user = User::factory()->create(['avatar' => 'avatars/old.png']);
        Storage::disk('private')->put('avatars/old.png', 'img');

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotNull($user->fresh()->avatar);
        $this->assertNotSame('avatars/old.png', $user->fresh()->avatar);
        Storage::disk('private')->assertMissing('avatars/old.png');
        Storage::disk('private')->assertExists($user->fresh()->avatar);
        $this->assertSame($user->fresh()->name, $user->name);
    }
}
