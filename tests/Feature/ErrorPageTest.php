<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_403_forbidden_renders_custom_error_page(): void
    {
        Route::get('tf/403', fn () => abort(403));

        $this->actingAs(User::factory()->create())
            ->get('tf/403')
            ->assertForbidden()
            ->assertSee('This one is private')
            ->assertSee('Access Denied');
    }

    public function test_404_renders_custom_error_page(): void
    {
        $this->get('tf/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page Not Found')
            ->assertSee('This page wandered off')
            ->assertSee('err-cover', false);
    }

    public function test_419_session_expired_renders_custom_error_page(): void
    {
        Route::get('tf/419', fn () => abort(419));

        $this->get('tf/419')
            ->assertStatus(419)
            ->assertSee('Session Expired')
            ->assertSee('Your session has expired');
    }

    public function test_429_rate_limited_renders_custom_error_page(): void
    {
        Route::get('tf/429', fn () => abort(429));

        $this->get('tf/429')
            ->assertStatus(429)
            ->assertSee('Too Many Requests')
            ->assertSee('Easy there, love');
    }

    public function test_500_renders_custom_error_page(): void
    {
        Route::get('tf/500', fn () => abort(500));

        $this->get('tf/500')
            ->assertStatus(500)
            ->assertSee('Something went wrong');
    }

    public function test_503_maintenance_renders_custom_error_page(): void
    {
        Route::get('tf/503', fn () => abort(503));

        $this->get('tf/503')
            ->assertStatus(503)
            ->assertSee('Maintenance')
            ->assertSee('Tiny maintenance break');
    }

    public function test_error_pages_render_dark_theme_for_dark_mode_users(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);

        $this->actingAs($user)
            ->get('tf/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('<html lang="en" data-theme="dark">', false);
    }

    public function test_error_pages_default_to_light_theme(): void
    {
        $this->get('tf/this-route-does-not-exist')
            ->assertNotFound()
            ->assertSee('<html lang="en" data-theme="light">', false);
    }
}
