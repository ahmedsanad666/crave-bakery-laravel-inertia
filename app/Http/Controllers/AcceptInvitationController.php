<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptAdminInvitationRequest;
use App\Http\Resources\AdminInvitationResource;
use App\Services\AdminInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AcceptInvitationController extends Controller
{
    public function __construct(
        private readonly AdminInvitationService $invitationService,
    ) {}

    public function show(string $token): Response
    {
        try {
            $invitation = $this->invitationService->findByToken($token);
        } catch (ValidationException $e) {
            return Inertia::render('Auth/AcceptInvitation', [
                'invitation' => null,
                'token' => $token,
                'error' => collect($e->errors())->flatten()->first(),
            ]);
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'invitation' => (new AdminInvitationResource($invitation))->resolve(),
            'token' => $token,
            'error' => null,
        ]);
    }

    public function accept(AcceptAdminInvitationRequest $request, string $token): RedirectResponse
    {
        $user = $this->invitationService->accept(
            $token,
            $request->validated('name'),
            $request->validated('password'),
        );

        Auth::login($user);

        return redirect()
            ->route($user->defaultRedirectRoute())
            ->with('success', 'Welcome to Crave Bakery Admin.');
    }
}
