<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformancePolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_timeline_preview_is_bounded(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 15) as $i) {
            $this->createMemory($user, [
                'title' => 'Journey memory '.$i,
                'memory_date' => '2024-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $this->assertSame(8, substr_count($response->getContent(), 'dash-timeline-item'));
        $response->assertSee('Journey memory 1');
        $response->assertSee('Journey memory 8');
        $response->assertDontSee('Journey memory 9');
    }

    public function test_dashboard_timeline_service_limits_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $i) {
            $this->createMemory($user, [
                'title' => 'Limited memory '.$i,
                'memory_date' => '2024-02-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $timeline = app(DashboardService::class)->timeline($user);

        $this->assertCount(8, $timeline);
    }

    public function test_sidebar_memory_badge_stays_in_sync_after_changes(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user);

        $this->actingAs($user)->get('/memories')->assertSee('class="badge">1', false);

        $this->createMemory($user, ['title' => 'Second memory', 'memory_date' => '2024-03-01']);
        $this->createMemory($user, ['title' => 'Third memory', 'memory_date' => '2024-03-02']);
        $this->createMemory($user, ['title' => 'Fourth memory', 'memory_date' => '2024-03-03']);

        $this->actingAs($user)->get('/dashboard')->assertSee('class="badge">4', false);
    }

    public function test_error_flash_is_rendered_on_authenticated_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'We could not delete your account right now.'])
            ->get('/memories');

        $response->assertOk();
        $response->assertSee('dash-alert-error', false);
        $response->assertSee('We could not delete your account right now.');
    }

    public function test_dashboard_cache_is_flushed_when_memory_is_created(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user);

        $this->actingAs($user)->get('/dashboard')->assertSee('class="badge">1', false);

        $this->createMemory($user, ['title' => 'Fresh memory', 'memory_date' => '2024-04-01']);

        $this->actingAs($user)->get('/dashboard')->assertSee('class="badge">2', false);
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
}
