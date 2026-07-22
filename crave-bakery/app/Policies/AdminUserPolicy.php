<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\User;

class AdminUserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }

    public function view(User $actor, AdminUser $adminUser): bool
    {
        return $actor->isSuperAdmin();
    }

    public function invite(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }

    public function updatePermissions(User $actor, AdminUser $adminUser): bool
    {
        return $actor->isSuperAdmin();
    }

    public function deactivate(User $actor, AdminUser $adminUser): bool
    {
        if (! $actor->isSuperAdmin()) {
            return false;
        }

        if ($actor->id === $adminUser->id) {
            return false;
        }

        return true;
    }

    public function delete(User $actor, AdminUser $adminUser): bool
    {
        if (! $actor->isSuperAdmin()) {
            return false;
        }

        if ($actor->id === $adminUser->id) {
            return false;
        }

        return true;
    }

    public function resendInvitation(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }
}
