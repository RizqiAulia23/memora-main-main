<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FullCrudValidationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function memory(User $user, array $overrides = []): Memory
    {
        return Memory::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Sunset in Bali',
            'description' => 'Golden hour by the shore.',
            'memory_date' => '2024-12-15',
        ], $overrides));
    }

    /* ================= MEMORY VALIDATION ================= */

    public function test_memory_requires_title_description_and_date(): void
    {
        $this->actingAs($this->user())
            ->post('/memories', [])
            ->assertSessionHasErrors(['title', 'description', 'memory_date']);
    }

    public function test_memory_rejects_image_with_non_image_extension(): void
    {
        Storage::fake('private');

        $this->actingAs($this->user())->post('/memories', [
            'title' => 'Bad image',
            'description' => 'desc',
            'memory_date' => '2024-01-01',
            'image' => UploadedFile::fake()->create('photo.txt', 100, 'text/plain'),
        ])->assertSessionHasErrors('image');
    }

    public function test_memory_rejects_mime_mismatch(): void
    {
        Storage::fake('private');

        $this->actingAs($this->user())->post('/memories', [
            'title' => 'Fake png',
            'description' => 'desc',
            'memory_date' => '2024-01-01',
            'image' => UploadedFile::fake()->create('photo.png', 100, 'text/plain'),
        ])->assertSessionHasErrors('image');
    }

    public function test_memory_rejects_image_larger_than_2mb(): void
    {
        Storage::fake('private');

        $this->actingAs($this->user())->post('/memories', [
            'title' => 'Big image',
            'description' => 'desc',
            'memory_date' => '2024-01-01',
            'image' => UploadedFile::fake()->create('big.png', 2500),
        ])->assertSessionHasErrors('image');
    }

    public function test_memory_rejects_invalid_date(): void
    {
        $this->actingAs($this->user())->post('/memories', [
            'title' => 'Bad date',
            'description' => 'desc',
            'memory_date' => 'not-a-date',
        ])->assertSessionHasErrors('memory_date');
    }

    public function test_memory_accepts_extreme_but_valid_dates(): void
    {
        $user = $this->user();

        foreach (['1900-01-01', '2100-12-31'] as $date) {
            $this->actingAs($user)->post('/memories', [
                'title' => 'Memory on '.$date,
                'description' => 'desc',
                'memory_date' => $date,
            ])->assertRedirect();
        }

        $this->assertSame(2, Memory::where('user_id', $user->id)->count());
    }

    public function test_memory_create_without_image_is_allowed(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/memories', [
            'title' => 'No photo',
            'description' => 'desc',
            'memory_date' => '2024-01-01',
        ])->assertRedirect();

        $this->assertNull(Memory::first()->image);
    }

    public function test_memory_title_exceeding_255_is_rejected(): void
    {
        $this->actingAs($this->user())->post('/memories', [
            'title' => str_repeat('a', 256),
            'description' => 'desc',
            'memory_date' => '2024-01-01',
        ])->assertSessionHasErrors('title');
    }

    public function test_memory_update_requires_valid_fields(): void
    {
        $user = $this->user();
        $memory = $this->memory($user);

        $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => '',
            'description' => '',
            'memory_date' => 'x',
        ])->assertSessionHasErrors(['title', 'description', 'memory_date']);

        $this->assertSame('Sunset in Bali', $memory->fresh()->title);
    }

    /* ================= LOVE LETTER VALIDATION ================= */

    public function test_letter_requires_title_content_mood_and_date(): void
    {
        $this->actingAs($this->user())
            ->post('/letters', [])
            ->assertSessionHasErrors(['title', 'content', 'mood', 'letter_date']);
    }

    public function test_letter_title_exceeding_255_is_rejected(): void
    {
        $this->actingAs($this->user())->post('/letters', [
            'title' => str_repeat('a', 256),
            'content' => 'x',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertSessionHasErrors('title');
    }

    public function test_letter_rejects_invalid_mood_and_date(): void
    {
        $this->actingAs($this->user())->post('/letters', [
            'title' => 'Bad',
            'content' => 'x',
            'mood' => '???',
            'letter_date' => 'nope',
        ])->assertSessionHasErrors(['mood', 'letter_date']);
    }

    public function test_letter_pin_flag_rejects_non_boolean_value(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/letters', [
            'title' => 'Pinned',
            'content' => 'x',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
            'is_pinned' => 'garbage',
        ])->assertSessionHasErrors('is_pinned');

        $this->assertDatabaseMissing('love_letters', ['title' => 'Pinned']);
    }

    /* ================= PROFILE / AVATAR VALIDATION ================= */

    public function test_profile_rejects_non_image_avatar(): void
    {
        Storage::fake('private');
        $user = $this->user();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Riz',
            'avatar' => UploadedFile::fake()->create('avatar.txt', 100, 'text/plain'),
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_profile_rejects_avatar_larger_than_2mb(): void
    {
        Storage::fake('private');
        $user = $this->user();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Riz',
            'avatar' => UploadedFile::fake()->create('avatar.png', 2500),
        ])->assertSessionHasErrors('avatar');
    }

    public function test_profile_rejects_future_relationship_date(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Riz',
            'relationship_date' => now()->addDay()->format('Y-m-d'),
        ])->assertSessionHasErrors('relationship_date');
    }

    public function test_profile_requires_name(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put('/profile', [
            'name' => '',
        ])->assertSessionHasErrors('name');
    }

    public function test_avatar_replacement_deletes_previous_file(): void
    {
        Storage::fake('private');

        $user = $this->user();
        $user->update(['avatar' => 'avatars/old.png']);
        Storage::disk('private')->put('avatars/old.png', 'img');

        $this->actingAs($user)->put('/profile', [
            'name' => 'Riz',
            'avatar' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect();

        $this->assertNotNull($user->fresh()->avatar);
        $this->assertNotSame('avatars/old.png', $user->fresh()->avatar);
        Storage::disk('private')->assertMissing('avatars/old.png');
        Storage::disk('private')->assertExists($user->fresh()->avatar);
    }
}
