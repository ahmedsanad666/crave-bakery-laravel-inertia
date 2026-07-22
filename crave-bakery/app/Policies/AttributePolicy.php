<?php

namespace App\Policies;

use App\Models\Attribute;
use App\Models\User;

class AttributePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attributes', 'view');
    }

    public function view(User $user, Attribute $attribute): bool
    {
        return $user->hasPermission('attributes', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attributes', 'create');
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $user->hasPermission('attributes', 'edit');
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->hasPermission('attributes', 'delete');
    }

    public function restore(User $user, Attribute $attribute): bool
    {
        return $user->hasPermission('attributes', 'delete');
    }

    public function forceDelete(User $user, Attribute $attribute): bool
    {
        return $user->hasPermission('attributes', 'delete');
    }
}
