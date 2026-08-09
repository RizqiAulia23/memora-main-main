<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_sidebar_memory_count_comes_from_composer_not_blade_query(): void
    {
        $sidebar = file_get_contents(resource_path('views/partials/dashboard-sidebar.blade.php'));
        $provider = file_get_contents(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringNotContainsString('memories()->count()', $sidebar);
        $this->assertStringNotContainsString('->count()', $sidebar);
        $this->assertStringContainsString('$memoryCount ?? 0', $sidebar);
        $this->assertStringContainsString('DashboardService::class', $provider);
        $this->assertStringContainsString("'partials.dashboard-sidebar'", $provider);
        $this->assertStringContainsString("stats(\$user)['total_memories']", $provider);
    }

    public function test_settings_error_flash_is_rendered_inline(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'We could not delete your account right now. Please try again.'])
            ->get('/settings');

        $response->assertOk();
        $response->assertSee('dash-alert dash-alert-error', false);
        $response->assertSee('We could not delete your account right now. Please try again.');
    }

    public function test_flash_alerts_partial_renders_success_and_error(): void
    {
        $partial = file_get_contents(resource_path('views/partials/flash-alerts.blade.php'));

        $this->assertStringContainsString('session(\'success\')', $partial);
        $this->assertStringContainsString('session(\'error\')', $partial);
        $this->assertStringContainsString('dash-alert-success', $partial);
        $this->assertStringContainsString('dash-alert-error', $partial);
    }

    public function test_instant_search_feedback_is_verifiable_without_browser(): void
    {
        $js = file_get_contents(public_path('js/dashboard.js'));

        $this->assertStringContainsString("if (!res.ok) throw new Error('Request failed');", $js);
        $this->assertStringContainsString("showToast('Search failed. Please try again.', 'error');", $js);
        $this->assertStringContainsString('hideResults()', $js);
        $this->assertStringContainsString("e.key === 'Escape'", $js);
        $this->assertStringContainsString("e.key === 'ArrowDown'", $js);
        $this->assertStringContainsString("e.key === 'Enter'", $js);
    }

    public function test_gallery_error_path_never_stalls_loading(): void
    {
        $js = file_get_contents(public_path('js/gallery.js'));

        $this->assertStringContainsString("if (!res.ok) throw new Error('Request failed');", $js);
        $this->assertStringContainsString("showToast('Could not load more photos. Please try again.', 'error');", $js);
        $this->assertStringContainsString('loading = false;', $js);
        $this->assertStringContainsString('hasMore = false;', $js);
        $this->assertStringContainsString('loadEl.hidden = true;', $js);
    }

    public function test_dashboard_timeline_query_only_loads_needed_columns(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user, ['title' => 'Column check', 'memory_date' => '2024-05-01']);

        DB::enableQueryLog();

        app(DashboardService::class)->timeline($user);

        $queries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(fn (string $q) => str_contains($q, 'memories'));

        $this->assertNotEmpty($queries);

        foreach ($queries as $query) {
            $this->assertStringContainsString('"user_id"', $query);
            $this->assertStringNotContainsString('select *', strtolower($query));
        }
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
