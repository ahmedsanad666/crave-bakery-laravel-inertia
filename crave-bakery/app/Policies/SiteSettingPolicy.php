<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('site_settings', 'view');
    }

    public function update(User $user, ?SiteSetting $siteSetting = null): bool
    {
        return $user->hasPermission('site_settings', 'edit');
    }
}
