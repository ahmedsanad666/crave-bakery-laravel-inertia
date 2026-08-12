<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Analytics;

class AnalyticsPolicy
{
    public function view(User $user, ?Analytics $analytics = null): bool
    {
        return $user->hasPermission('analytics', 'view');
    }
}
