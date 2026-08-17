<?php

namespace Tests\Feature;

use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullBoundaryPaginationTest extends TestCase
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

    public function test_memory_description_4999_characters_is_accepted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memories', [
            'title' => 'Edge case',
            'description' => str_repeat('a', 4999),
            'memory_date' => '2024-01-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('memories', ['title' => 'Edge case']);
    }

    public function test_letter_content_49999_characters_is_accepted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/letters', [
            'title' => 'Edge letter',
            'content' => str_repeat('x', 49999),
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('love_letters', ['title' => 'Edge letter']);
    }

    public function test_memories_page_edge_values_are_handled(): void
    {
        $user = User::factory()->create();
        $this->memory($user);

        foreach (['0', '-1', '999999', 'abc', '1.5'] as $page) {
            $response = $this->actingAs($user)->get('/memories?page='.$page);
            $this->assertSame(200, $response->getStatusCode(), "page={$page} should not crash");
        }

        // Realistic pages still show the memory
        foreach (['0', '-1', 'abc', '1.5'] as $page) {
            $response = $this->actingAs($user)->get('/memories?page='.$page);
            $this->assertStringContainsString('Sunset in Bali', $response->getContent(), "page={$page} should show the memory");
        }

        // Out-of-range page shows empty pagination but stays 200
        $this->assertStringNotContainsString('Sunset in Bali', $this->actingAs($user)->get('/memories?page=999999')->getContent());
    }

    public function test_letters_page_edge_values_are_handled(): void
    {
        $user = User::factory()->create();
        LoveLetter::create([
            'user_id' => $user->id,
            'title' => 'Letter one',
            'content' => '<p>x</p>',
            'mood' => 'love',
            'letter_date' => '2024-01-01',
        ]);

        foreach (['0', '-1', '999999', 'abc'] as $page) {
            $response = $this->actingAs($user)->get('/letters?page='.$page);
            $this->assertSame(200, $response->getStatusCode(), "page={$page} should not crash");
        }
    }

    public function test_timeline_handles_extreme_year_values(): void
    {
        $user = User::factory()->create();
        $this->memory($user, ['memory_date' => '2024-06-15']);

        foreach (['999999', '-5', '0', 'abc', '1900'] as $year) {
            $response = $this->actingAs($user)->get('/timeline?year='.$year);
            $this->assertSame(200, $response->getStatusCode(), "year={$year} should not crash");
        }
    }

    public function test_calendar_handles_extreme_month_values(): void
    {
        $user = User::factory()->create();

        foreach (['1800-01', '2300-12', '0000-01', '2024-13', '2024-00'] as $month) {
            $response = $this->actingAs($user)->get('/calendar?month='.$month);
            $this->assertSame(200, $response->getStatusCode(), "month={$month} should not crash");
        }
    }

    public function test_calendar_on_date_with_no_memories_returns_empty_html(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/calendar/date?date=2024-12-25');

        $response->assertOk()->assertJsonStructure(['date', 'html']);
    }

    public function test_search_with_very_long_query_is_handled(): void
    {
        $user = User::factory()->create();
        $this->memory($user);

        $response = $this->actingAs($user)->get('/search?q='.str_repeat('a', 5000));

        $response->assertOk();
    }

    public function test_timeline_empty_state_is_shown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/timeline');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('empty', strtolower($content));
    }

    public function test_memories_empty_state_is_shown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/memories');

        $response->assertOk();
        $content = strtolower($response->getContent());
        $this->assertTrue(
            str_contains($content, 'empty') || str_contains($content, 'no memories'),
            'Expected an empty-state message on /memories'
        );
    }

    public function test_letters_empty_state_is_shown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/letters');

        $response->assertOk();
        $content = strtolower($response->getContent());
        $this->assertTrue(
            str_contains($content, 'empty') || str_contains($content, 'no letters'),
            'Expected an empty-state message on /letters'
        );
    }

    public function test_gallery_empty_state_is_shown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/gallery');

        $response->assertOk();
        $content = strtolower($response->getContent());
        $this->assertTrue(
            str_contains($content, 'empty') || str_contains($content, 'no photos'),
            'Expected an empty-state message on /gallery'
        );
    }
}
