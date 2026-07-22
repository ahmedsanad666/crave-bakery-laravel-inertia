<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('orders', 'view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('orders', 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('orders', 'update_status');
    }

    public function delete(User $user, Order $order): bool
    {
        return false;
    }

    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }

    public function refund(User $user, Order $order): bool
    {
        return $user->hasPermission('orders', 'refund');
    }
}
