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
            ->assertSee('This page wandered off');
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
}
