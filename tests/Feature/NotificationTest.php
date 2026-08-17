<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ImportantDateReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function sendNotification(User $user, string $title = 'You are loved'): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ImportantDateReminderNotification::class,
            'data' => [
                'title' => $title,
                'message' => 'Just a reminder',
                'url' => '/calendar',
            ],
        ]);
    }

    public function test_notifications_page_requires_auth(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }

    public function test_notifications_page_shows_only_own_notifications(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $this->sendNotification($a, 'For A');
        $this->sendNotification($b, 'For B');

        $this->actingAs($a)->get('/notifications')
            ->assertOk()
            ->assertSee('For A')
            ->assertDontSee('For B');
    }

    public function test_notifications_page_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('No notifications yet');
    }

    public function test_unread_notifications_show_unread_dot(): void
    {
        $user = User::factory()->create();
        $this->sendNotification($user);
        $read = $this->sendNotification($user, 'Already read');
        $read->markAsRead();

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('notif-page-dot', false)
            ->assertSee('Just a reminder');
    }

    public function test_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->sendNotification($user);

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect('/calendar');

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_mark_read_falls_back_to_index_when_no_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->sendNotification($user);
        $notification->data = ['title' => 'No link', 'message' => 'x'];
        $notification->save();

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect('/notifications');
    }

    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $notification = $this->sendNotification($a);

        $this->actingAs($b)
            ->post("/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $this->sendNotification($user);
        $this->sendNotification($user);
        $this->sendNotification($user);

        $this->actingAs($user)
            ->from('/notifications')
            ->post('/notifications/read-all')
            ->assertRedirect('/notifications')
            ->assertSessionHas('success');

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_mark_all_as_read_with_no_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/notifications')
            ->post('/notifications/read-all')
            ->assertRedirect('/notifications');
    }

    public function test_read_all_does_not_touch_other_users_notifications(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $this->sendNotification($a, 'For A');
        $bNotification = $this->sendNotification($b, 'For B');

        $this->actingAs($a)->post('/notifications/read-all');

        $this->assertNull($bNotification->fresh()->read_at);
        $this->assertNotNull($a->notifications()->first()->read_at);
    }

    public function test_created_notifications_appear_with_title_and_message(): void
    {
        $user = User::factory()->create();
        $user->notify(new ImportantDateReminderNotification('Test Partner', 'Our anniversary', '2026-08-15'));

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('Test Partner')
            ->assertSee('Our anniversary');
    }

    public function test_read_does_not_redirect_to_protocol_relative_external_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->sendNotification($user);
        $notification->data = ['title' => 'x', 'message' => 'x', 'url' => '//evil.example.com'];
        $notification->save();

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect('/notifications');
    }

    public function test_read_does_not_redirect_to_absolute_external_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->sendNotification($user);
        $notification->data = ['title' => 'x', 'message' => 'x', 'url' => 'https://evil.example.com/phish'];
        $notification->save();

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect('/notifications');
    }

    public function test_read_redirects_to_absolute_same_host_url(): void
    {
        $user = User::factory()->create();
        $notification = $this->sendNotification($user);
        $notification->data = ['title' => 'x', 'message' => 'x', 'url' => url('/memories')];
        $notification->save();

        $this->actingAs($user)
            ->post("/notifications/{$notification->id}/read")
            ->assertRedirect(route('memories.index'));
    }

    public function test_account_deletion_removes_notifications(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $this->sendNotification($user);
        $this->sendNotification($user);

        $this->actingAs($user)->delete('/settings/account', ['password' => 'password123']);

        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $user->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
