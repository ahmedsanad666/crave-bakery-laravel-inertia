<?php

namespace Database\Seeders;

use App\Models\AdminInvitation;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminInvitationSeeder extends Seeder
{
    public function run(): void
    {
        $inviter = User::query()->where('role', 'super_admin')->first();

        if (! $inviter) {
            $this->command?->warn('AdminInvitationSeeder skipped: no super_admin found.');

            return;
        }

        $email = 'pending.admin@example.com';

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $existing = AdminInvitation::query()
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existing) {
            return;
        }

        AdminInvitation::query()->updateOrCreate(
            ['email' => $email, 'accepted_at' => null],
            [
                'role' => 'admin',
                'permissions' => AdminPermissions::fromTemplate('full_admin'),
                'token' => Str::random(64),
                'invited_by' => $inviter->id,
                'expires_at' => now()->addDays(7),
            ],
        );
    }
}
