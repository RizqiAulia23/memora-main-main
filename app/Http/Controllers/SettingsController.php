<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\AccountService;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly StorageService $storage,
        private readonly AccountService $account,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $settings = $user->getSettings();
        $storageUsed = $this->storage->formatBytes(
            Cache::remember(
                'storage.usage.'.$user->id,
                now()->addMinutes(5),
                fn () => $this->storage->usageForUser($user),
            ),
        );

        return view('settings.index', compact('user', 'settings', 'storageUsed'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $settings = $user->getSettings();

        $settings->update([
            'theme' => $request->validated('theme'),
            'notifications_enabled' => $request->boolean('notifications_enabled'),
        ]);

        return back()->with('success', 'Your settings have been saved.');
    }

    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->account->delete($user);
            Cache::forget('storage.usage.'.$user->id);
        } catch (\Throwable $exception) {
            Log::warning('deleteAccount failed: '.$exception->getMessage());

            return redirect()->route('settings.index')->with('error', 'We could not delete your account right now. Please try again.');
        }

        // Prevent the guarded user's remember token from re-saving the already deleted row.
        $user->remember_token = null;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted. Goodbye.');
    }
}
