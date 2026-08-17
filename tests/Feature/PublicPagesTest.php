<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_has_no_dead_form(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('contact-form', false)
            ->assertSee('Get in Touch')
            ->assertSee(route('register'));
    }

    public function test_login_page_has_no_dead_controls(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Continue with Google')
            ->assertDontSee('forgot-link', false);
    }

    public function test_register_page_has_no_dead_controls(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertDontSee('Continue with Google');
    }

    public function test_public_pages_have_no_dead_anchor_links(): void
    {
        foreach (['/', '/features', '/about', '/contact'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('href="#"', false);
        }
    }

    public function test_features_ctas_point_to_register(): void
    {
        $this->get('/features')
            ->assertOk()
            ->assertSee(url('/register'), false);
    }
}
