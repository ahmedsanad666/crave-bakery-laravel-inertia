<?php

namespace App\Services;

use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminInvitationService
{
    /**
     * @param  array<string, mixed>|null  $permissions
     */
    public function invite(
        string $email,
        string $role,
        User $invitedBy,
        ?string $template = null,
        ?array $permissions = null,
    ): AdminInvitation {
        $email = Str::lower(trim($email));

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'A user with this email already exists.',
            ]);
        }

        $pending = AdminInvitation::query()
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pending) {
            throw ValidationException::withMessages([
                'email' => 'A pending invitation already exists for this email.',
            ]);
        }

        if (! in_array($role, ['admin', 'super_admin'], true)) {
            throw ValidationException::withMessages([
                'role' => 'Invalid role.',
            ]);
        }

        $matrix = $this->resolvePermissions($role, $template, $permissions);

        $invitation = AdminInvitation::query()->create([
            'email' => $email,
            'role' => $role,
            'permissions' => $matrix,
            'token' => Str::random(64),
            'invited_by' => $invitedBy->id,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new AdminInvitationMail($invitation));

        return $invitation->load('invitedBy:id,name,email');
    }

    public function resend(AdminInvitation $invitation): AdminInvitation
    {
        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation has already been accepted.',
            ]);
        }

        $invitation->update([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new AdminInvitationMail($invitation->fresh()));

        return $invitation->fresh(['invitedBy:id,name,email']);
    }

    public function revoke(AdminInvitation $invitation): void
    {
        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'invitation' => 'Cannot revoke an accepted invitation.',
            ]);
        }

        $invitation->delete();
    }

    /**
     * @return Collection<int, AdminInvitation>
     */
    public function listPending(): Collection
    {
        return AdminInvitation::query()
            ->with('invitedBy:id,name,email')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    public function findByToken(string $token): AdminInvitation
    {
        $invitation = AdminInvitation::query()
            ->where('token', $token)
            ->first();

        if (! $invitation) {
            throw ValidationException::withMessages([
                'token' => 'Invitation not found.',
            ]);
        }

        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has already been accepted.',
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has expired.',
            ]);
        }

        return $invitation;
    }

    public function accept(string $token, string $name, string $password): User
    {
        return DB::transaction(function () use ($token, $name, $password) {
            $invitation = $this->findByToken($token);

            if (User::query()->where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'A user with this email already exists.',
                ]);
            }

            $user = User::query()->create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => $password,
                'role' => $invitation->role,
                'status' => 'active',
                'permissions' => $invitation->role === 'super_admin'
                    ? null
                    : AdminPermissions::normalize($invitation->permissions),
                'email_verified_at' => now(),
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>|null  $permissions
     * @return array<string, array<string, bool>>|null
     */
    private function resolvePermissions(string $role, ?string $template, ?array $permissions): ?array
    {
        if ($role === 'super_admin') {
            return null;
        }

        if (filled($template)) {
            return AdminPermissions::fromTemplate($template);
        }

        if (is_array($permissions)) {
            return AdminPermissions::normalize($permissions);
        }

        return AdminPermissions::emptyMatrix();
    }
}
