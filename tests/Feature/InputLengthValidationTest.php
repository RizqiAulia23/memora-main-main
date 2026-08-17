<?php

namespace Tests\Feature;

use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputLengthValidationTest extends TestCase
{
    use RefreshDatabase;

    /* ========================================
       Memory Description Length Tests
       ======================================== */

    public function test_memory_description_at_max_length_5000_is_accepted(): void
    {
        $user = User::factory()->create();
        $description = str_repeat('a', 5000);

        $this->actingAs($user)->post('/memories', [
            'title' => 'Test Memory',
            'description' => $description,
            'memory_date' => '2024-01-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('memories', [
            'user_id' => $user->id,
            'title' => 'Test Memory',
            'description' => $description,
        ]);
    }

    public function test_memory_description_exceeding_max_length_5000_is_rejected(): void
    {
        $user = User::factory()->create();
        $description = str_repeat('a', 5001);

        $this->actingAs($user)->post('/memories', [
            'title' => 'Test Memory',
            'description' => $description,
            'memory_date' => '2024-01-01',
        ])->assertSessionHasErrors('description');

        $this->assertDatabaseMissing('memories', [
            'user_id' => $user->id,
            'title' => 'Test Memory',
        ]);
    }

    public function test_memory_update_description_at_max_length_5000_is_accepted(): void
    {
        $user = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $user->id,
            'title' => 'Test Memory',
            'description' => 'Original description.',
            'memory_date' => '2024-01-01',
        ]);

        $newDescription = str_repeat('b', 5000);

        $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => 'Test Memory',
            'description' => $newDescription,
            'memory_date' => '2024-01-01',
        ])->assertRedirect();

        $memory->refresh();
        $this->assertEquals($newDescription, $memory->description);
    }

    public function test_memory_update_description_exceeding_max_length_5000_is_rejected(): void
    {
        $user = User::factory()->create();
        $memory = Memory::create([
            'user_id' => $user->id,
            'title' => 'Test Memory',
            'description' => 'Original description.',
            'memory_date' => '2024-01-01',
        ]);

        $newDescription = str_repeat('b', 5001);

        $this->actingAs($user)->patch("/memories/{$memory->id}", [
            'title' => 'Test Memory',
            'description' => $newDescription,
            'memory_date' => '2024-01-01',
        ])->assertSessionHasErrors('description');

        $memory->refresh();
        $this->assertEquals('Original description.', $memory->description);
    }

    /* ========================================
       Love Letter Content Length Tests
       ======================================== */

    public function test_love_letter_content_at_max_length_50000_is_accepted(): void
    {
        $user = User::factory()->create();
        $content = str_repeat('x', 50000);

        $this->actingAs($user)->post('/letters', [
            'title' => 'Love Letter',
            'content' => $content,
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('love_letters', [
            'user_id' => $user->id,
            'title' => 'Love Letter',
            'content' => $content,
        ]);
    }

    public function test_love_letter_content_exceeding_max_length_50000_is_rejected(): void
    {
        $user = User::factory()->create();
        $content = str_repeat('x', 50001);

        $this->actingAs($user)->post('/letters', [
            'title' => 'Love Letter',
            'content' => $content,
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertSessionHasErrors('content');

        $this->assertDatabaseMissing('love_letters', [
            'user_id' => $user->id,
            'title' => 'Love Letter',
        ]);
    }

    public function test_love_letter_update_content_at_max_length_50000_is_accepted(): void
    {
        $user = User::factory()->create();
        $letter = LoveLetter::create([
            'user_id' => $user->id,
            'title' => 'Love Letter',
            'content' => 'Original content.',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ]);

        $newContent = str_repeat('y', 50000);

        $this->actingAs($user)->patch("/letters/{$letter->id}", [
            'title' => 'Love Letter',
            'content' => $newContent,
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertRedirect();

        $letter->refresh();
        $this->assertEquals($newContent, $letter->content);
    }

    public function test_love_letter_update_content_exceeding_max_length_50000_is_rejected(): void
    {
        $user = User::factory()->create();
        $letter = LoveLetter::create([
            'user_id' => $user->id,
            'title' => 'Love Letter',
            'content' => 'Original content.',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ]);

        $newContent = str_repeat('y', 50001);

        $this->actingAs($user)->patch("/letters/{$letter->id}", [
            'title' => 'Love Letter',
            'content' => $newContent,
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertSessionHasErrors('content');

        $letter->refresh();
        $this->assertEquals('Original content.', $letter->content);
    }
}
