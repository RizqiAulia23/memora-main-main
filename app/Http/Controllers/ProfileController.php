<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
    ) {}

    public function show(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profile->updateProfile(
            $request->user(),
            $request->validated(),
            $request->file('avatar'),
        );

        return back()->with('success', 'Your profile has been updated.');
    }

    public function removeAvatar(): RedirectResponse
    {
        $this->profile->removeAvatar(auth()->user());

        return back()->with('success', 'Avatar removed.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Your password has been updated.');
    }
}
