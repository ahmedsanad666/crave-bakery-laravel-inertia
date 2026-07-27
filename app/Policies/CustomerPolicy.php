<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers', 'view');
    }

    public function view(User $user, User $customer): bool
    {
        if (! $customer->isUser()) {
            return false;
        }

        return $user->hasPermission('customers', 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $customer): bool
    {
        if (! $customer->isUser()) {
            return false;
        }

        return $user->hasPermission('customers', 'edit');
    }

    public function delete(User $user, User $customer): bool
    {
        return false;
    }

    public function restore(User $user, User $customer): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $customer): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('customers', 'export');
    }
}
