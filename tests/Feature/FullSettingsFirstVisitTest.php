<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for BUG-001: on the FIRST visit to /settings,
 * User::getSettings() created the settings row via settings()->create(['user_id' => ...])
 * and the returned in-memory model had theme = null (SQLite column default 'light'
 * was not reflected), so the form rendered WITHOUT a selected theme radio and saving
 * without touching the theme failed validation ('The theme field is required.').
 *
 * Fixed in User::getSettings() by re-reading the created row (fresh()) so the model
 * reflects the column defaults (theme = 'light', notifications_enabled = true).
 */
class FullSettingsFirstVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_settings_visit_renders_checked_theme_radio(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/settings')
            ->assertOk()
            ->assertSee('value="light" checked', false);
    }

    public function test_save_with_theme_selected_works_on_first_visit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/settings')->assertOk();

        $user->refresh();

        $this->put('/settings', [
            'theme' => 'light',
        ])->assertRedirect();

        $this->get('/settings')->assertSee('Your settings have been saved.');
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'theme' => 'light',
        ]);
    }

    public function test_get_settings_returns_light_for_user_without_settings(): void
    {
        $user = User::factory()->create();

        $this->assertSame('light', $user->getSettings()->theme);
    }

    public function test_get_settings_returns_light_default_notifications_for_user_without_settings(): void
    {
        $user = User::factory()->create();

        $settings = $user->getSettings();

        $this->assertSame('light', $settings->theme);
        $this->assertTrue($settings->notifications_enabled);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'theme' => 'light',
            'notifications_enabled' => true,
        ]);
    }

    public function test_get_settings_returns_stored_dark_theme(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);

        $this->assertSame('dark', $user->getSettings()->theme);
    }

    public function test_get_settings_returns_stored_light_theme(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'light', 'notifications_enabled' => false]);

        $this->assertSame('light', $user->getSettings()->theme);
    }

    public function test_get_settings_does_not_overwrite_stored_theme(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'dark', 'notifications_enabled' => true]);

        $this->actingAs($user);

        $this->get('/settings')->assertOk()->assertSee('value="dark" checked', false);

        $this->assertSame('dark', $user->getSettings()->theme);
        $this->assertSame('dark', UserSettings::where('user_id', $user->id)->value('theme'));
    }
}
