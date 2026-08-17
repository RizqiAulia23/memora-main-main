<?php

namespace App\Providers;

use App\Models\Favorite;
use App\Models\LoveLetter;
use App\Models\Memory;
use App\Observers\DashboardCacheObserver;
use App\Policies\NotificationPolicy;
use App\Services\DashboardService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Memory::observe(DashboardCacheObserver::class);
        LoveLetter::observe(DashboardCacheObserver::class);
        Favorite::observe(DashboardCacheObserver::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);

        View::composer('*', function ($view) {
            $user = auth()->user();

            $view->with('theme', $user?->settings?->theme ?? 'light');
        });

        View::composer('partials.dashboard-sidebar', function ($view) {
            $user = auth()->user();

            if ($user) {
                $view->with('memoryCount', app(DashboardService::class)->stats($user)['total_memories']);
            }
        });

        View::composer('partials.dashboard-topbar', function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            $stats = app(DashboardService::class)->stats($user);

            $view->with([
                'unreadNotificationsCount' => $stats['unread_notifications'],
                'recentNotifications' => $user->notifications()->latest()->limit(8)->get(),
            ]);
        });
    }
}
