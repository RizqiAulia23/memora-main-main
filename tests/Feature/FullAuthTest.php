<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_email_registration_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@memorify.com']);

        $response = $this->post('/register', [
            'name' => 'Second User',
            'email' => 'taken@memorify.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'taken@memorify.com')->count());
    }

    public function test_login_with_wrong_password_shows_error(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_valid_email_and_password(): void
    {
        $this->post('/login', [
            'email' => 'not-an-email',
            'password' => '',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_register_requires_password_confirmation_match(): void
    {
        $this->post('/register', [
            'name' => 'Riz',
            'email' => 'riz@memorify.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'riz@memorify.com']);
    }

    public function test_logout_only_accepts_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/logout')->assertStatus(405);
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_still_view_public_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk();
        $this->actingAs($user)->get('/about')->assertOk();
        $this->actingAs($user)->get('/contact')->assertOk();
        $this->actingAs($user)->get('/features')->assertOk();
    }

    public function test_authenticated_user_can_open_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertOk();
        $this->actingAs($user)->get('/register')->assertOk();
    }

    public function test_session_persists_across_requests(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->get('/dashboard')->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->get('/memories')->assertOk();
    }

    public function test_guest_cannot_submit_favorite_toggle(): void
    {
        $this->post('/memories/1/favorite')->assertRedirect('/login');
    }

    public function test_register_password_below_minimum_is_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Short',
            'email' => 'short@memorify.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ])->assertSessionHasErrors('password');
    }

    public function test_login_with_remember_me_keeps_session(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($this->app['auth']->guard()->user()->remember_token ?? null);
    }
}
