<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempFlashProbeTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_after_register_renders_on_login_page(): void
    {
        $this->post('/register', [
            'name' => 'Probe',
            'email' => 'probe@memorify.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('login'));

        $this->get(route('login'))->assertSee('Account created successfully. Please sign in.');
    }

    public function test_flash_after_memory_create_renders_on_show_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memories', [
            'title' => 'Flash Probe',
            'description' => 'desc',
            'memory_date' => '2024-10-10',
        ])->assertRedirect();

        $this->get('/memories/1')->assertSee('Memory created successfully.');
    }

    public function test_flash_after_settings_update_renders_on_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', [
            'theme' => 'light',
            'notifications_enabled' => true,
        ])->assertRedirect();

        $this->get('/settings')->assertSee('Your settings have been saved.');
    }
}
