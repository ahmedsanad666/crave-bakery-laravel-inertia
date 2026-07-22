<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminPasswordRequest;
use App\Http\Requests\UpdateAdminProfileRequest;
use App\Services\AdminProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AdminProfileService $profileService,
    ) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Admin/Profile/Edit', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => AdminProfileService::avatarUrl($user->avatar),
                'role' => $user->role,
            ],
        ]);
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        $this->profileService->update(
            $request->user(),
            [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'avatar' => $request->file('avatar'),
                'remove_avatar' => $request->boolean('remove_avatar'),
            ],
        );

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(UpdateAdminPasswordRequest $request): RedirectResponse
    {
        $this->profileService->updatePassword(
            $request->user(),
            $request->validated('password'),
        );

        return back()->with('success', 'Password updated.');
    }
}
