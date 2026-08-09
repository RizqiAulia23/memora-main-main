<?php

namespace App\Providers;

use App\Models\Favorite;
use App\Models\LoveLetter;
use App\Models\Memory;
use App\Observers\DashboardCacheObserver;
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

        View::composer('*', function ($view) {
            $user = auth()->user();

            $view->with('theme', $user?->settings?->theme ?? 'light');
        });
    }
}
