<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAndLightboxTest extends TestCase
{
    use RefreshDatabase;

    private function createMemory(User $user, array $overrides = []): Memory
    {
        return Memory::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Sunset in Bali',
            'description' => 'Golden hour by the shore.',
            'memory_date' => '2024-12-15',
        ], $overrides));
    }

    public function test_gallery_lightbox_starts_hidden_in_html(): void
    {
        $user = User::factory()->create();
        $this->createMemory($user, ['image' => 'memories/a.png']);

        $html = $this->actingAs($user)->get('/gallery')->getContent();

        $this->assertStringContainsString('id="gal-lightbox"', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('<div class="gal-lightbox" id="gal-lightbox" role="dialog" aria-modal="true" aria-label="Photo preview" hidden>', $html);
        $this->assertStringContainsString('data-lightbox-close', $html);
        $this->assertStringContainsString('data-lightbox-prev', $html);
        $this->assertStringContainsString('data-lightbox-next', $html);
        $this->assertStringContainsString('data-lightbox-img', $html);
        $this->assertStringContainsString('data-lightbox-link', $html);
    }

    public function test_hidden_attribute_is_honored_by_css(): void
    {
        $css = file_get_contents(public_path('css/base.css'));

        $this->assertMatchesRegularExpression('/\[hidden\]\s*{[^}]*display:\s*none\s*!important/s', $css);
    }

    public function test_gallery_js_ignores_download_link_when_opening_lightbox(): void
    {
        $js = file_get_contents(public_path('js/gallery.js'));

        $this->assertStringContainsString("if (e.target.closest('.gal-download')) return;", $js);
        $this->assertStringContainsString("e.target.closest('[data-gallery-item]')", $js);
        $this->assertStringContainsString('openLightbox(index);', $js);
    }

    public function test_gallery_js_binds_lightbox_close_overlay_and_keyboard(): void
    {
        $js = file_get_contents(public_path('js/gallery.js'));

        $this->assertStringContainsString("lightbox.querySelector('[data-lightbox-close]').addEventListener('click', closeLightbox);", $js);
        $this->assertStringContainsString('if (e.target === lightbox) closeLightbox();', $js);
        $this->assertStringContainsString("if (e.key === 'Escape') closeLightbox();", $js);
        $this->assertStringContainsString("if (e.key === 'ArrowLeft') step(-1);", $js);
        $this->assertStringContainsString("if (e.key === 'ArrowRight') step(1);", $js);
        $this->assertStringContainsString("prev.addEventListener('click', function () { step(-1); });", $js);
        $this->assertStringContainsString("next.addEventListener('click', function () { step(1); });", $js);
        $this->assertStringContainsString('document.body.style.overflow = \'hidden\';', $js);
    }

    public function test_theme_renders_on_gallery_page_for_dark_setting(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);
        $this->createMemory($user, ['image' => 'memories/a.png']);

        $this->actingAs($user)
            ->get('/gallery')
            ->assertSee('<html lang="en" data-theme="dark">', false);
    }

    public function test_theme_renders_on_settings_page_for_light_setting(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'light', 'notifications_enabled' => true]);

        $this->actingAs($user)
            ->get('/settings')
            ->assertSee('<html lang="en" data-theme="light">', false)
            ->assertSee('<input type="radio" name="theme" value="light" checked', false)
            ->assertSee('value="dark"', false);
    }

    public function test_css_assets_are_served_with_cache_busting_version(): void
    {
        $version = filemtime(public_path('css/base.css'));

        $this->assertNotFalse($version);
        $this->assertSame(
            asset('css/base.css').'?v='.$version,
            assetv('css/base.css')
        );
    }

    public function test_pages_use_versioned_asset_urls_for_css_and_js(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/dashboard')->getContent();

        $this->assertMatchesRegularExpression('/css\/base\.css\?v=\d+/', $html);
        $this->assertMatchesRegularExpression('/css\/dashboard\.css\?v=\d+/', $html);
        $this->assertMatchesRegularExpression('/js\/main\.js\?v=\d+/', $html);
        $this->assertMatchesRegularExpression('/js\/dashboard\.js\?v=\d+/', $html);
    }

    public function test_views_do_not_reference_unversioned_assets(): void
    {
        $views = glob(resource_path('views/**/*.blade.php')) ?: [];

        foreach ($views as $view) {
            $content = file_get_contents($view);
            $this->assertDoesNotMatchRegularExpression("/asset\('(css|js)\//", $content, "Unversioned asset found in {$view}");
        }
    }
}
