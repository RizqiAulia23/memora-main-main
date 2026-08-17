<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DatabaseNotification::class);

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->authorize('view', $notification);

        if (! $notification->read_at) {
            $notification->markAsRead();
            $this->dashboard->flush($request->user());
        }

        $url = $notification->data['url'] ?? null;

        if (is_string($url) && $url !== '' && $this->isSafeRedirectUrl($url, $request)) {
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    private function isSafeRedirectUrl(string $url, Request $request): bool
    {
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\')) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->each->markAsRead();

        $this->dashboard->flush($request->user());

        return back()->with('success', 'All notifications marked as read.');
    }
}
