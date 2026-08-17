<?php

namespace Tests\Feature;

use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FullPerformanceTest extends TestCase
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

    private function letter(User $user, array $overrides = []): LoveLetter
    {
        return LoveLetter::create(array_merge([
            'user_id' => $user->id,
            'title' => 'My letter',
            'content' => '<p>words</p>',
            'mood' => 'love',
            'letter_date' => '2024-12-15',
        ], $overrides));
    }

    private function queryCountFor(callable $callback): int
    {
        DB::enableQueryLog();
        $callback();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_memories_index_does_not_have_n_plus_1(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 15) as $i) {
            $this->memory($user, ['title' => 'Memory '.$i, 'memory_date' => '2024-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)]);
        }

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/memories'));

        $this->assertLessThan(15, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_gallery_index_does_not_have_n_plus_1(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $i) {
            $this->memory($user, [
                'title' => 'Photo '.$i,
                'image' => 'memories/photo-'.$i.'.png',
                'memory_date' => '2024-01-01',
            ]);
        }

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/gallery'));

        $this->assertLessThan(12, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_letters_index_does_not_have_n_plus_1(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $i) {
            $this->letter($user, ['title' => 'Letter '.$i]);
        }

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/letters'));

        $this->assertLessThan(12, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_favorites_index_does_not_have_n_plus_1(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 10) as $i) {
            $memory = $this->memory($user, ['title' => 'Fav '.$i]);
            $user->favorites()->create(['memory_id' => $memory->id]);
        }

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/favorites'));

        $this->assertLessThan(10, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_dashboard_page_has_bounded_query_count(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $i) {
            $this->memory($user, ['title' => 'Dash '.$i, 'memory_date' => '2024-05-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)]);
        }
        $this->letter($user);

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/dashboard'));

        $this->assertLessThan(20, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_timeline_has_no_n_plus_1_query_growth(): void
    {
        $user = User::factory()->create();
        $this->memory($user, ['title' => 'T 1', 'memory_date' => '2023-06-15']);

        $queriesWithOne = $this->timelineDataQueryCount($user);

        foreach (range(2, 20) as $i) {
            $this->memory($user, ['title' => 'T '.$i, 'memory_date' => '2023-06-15']);
        }

        $queriesWithTwenty = $this->timelineDataQueryCount($user);

        $this->assertLessThanOrEqual($queriesWithOne + 1, $queriesWithTwenty,
            "Query count grew from {$queriesWithOne} to {$queriesWithTwenty} for 20 memories (N+1 suspected)"
        );
    }

    private function timelineDataQueryCount(User $user): int
    {
        DB::enableQueryLog();
        $this->actingAs($user)->get('/timeline');
        $count = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'memories') && ! str_contains($q['query'], 'count('))
            ->count();
        DB::disableQueryLog();

        return $count;
    }

    public function test_memory_search_uses_indexed_columns_with_bounded_queries(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $i) {
            $this->memory($user, ['title' => 'Coffee memory '.$i]);
        }

        $queries = $this->queryCountFor(fn () => $this->actingAs($user)->get('/memories?search=coffee'));

        $this->assertLessThan(10, $queries, "Expected bounded queries, got {$queries}");
    }

    public function test_favorites_toggle_does_not_leak_queries(): void
    {
        $user = User::factory()->create();
        $memory = $this->memory($user);

        $queries = $this->queryCountFor(function () use ($user, $memory) {
            $this->actingAs($user)->postJson("/memories/{$memory->id}/favorite");
        });

        $this->assertLessThan(10, $queries, "Expected bounded queries, got {$queries}");
    }
}
