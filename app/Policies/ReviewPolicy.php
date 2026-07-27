<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reviews', 'view');
    }

    public function view(User $user, Review $review): bool
    {
        return $user->hasPermission('reviews', 'view');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->hasPermission('reviews', 'approve');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->hasPermission('reviews', 'delete');
    }

    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }

    public function respond(User $user, Review $review): bool
    {
        return $user->hasPermission('reviews', 'respond');
    }
}
