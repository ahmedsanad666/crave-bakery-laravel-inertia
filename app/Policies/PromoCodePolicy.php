<?php

namespace App\Policies;

use App\Models\PromoCode;
use App\Models\User;

class PromoCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('promo_codes', 'view');
    }

    public function view(User $user, PromoCode $promoCode): bool
    {
        return $user->hasPermission('promo_codes', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('promo_codes', 'create');
    }

    public function update(User $user, PromoCode $promoCode): bool
    {
        return $user->hasPermission('promo_codes', 'edit');
    }

    public function delete(User $user, PromoCode $promoCode): bool
    {
        return $user->hasPermission('promo_codes', 'delete');
    }

    public function restore(User $user, PromoCode $promoCode): bool
    {
        return $user->hasPermission('promo_codes', 'delete');
    }

    public function forceDelete(User $user, PromoCode $promoCode): bool
    {
        return $user->hasPermission('promo_codes', 'delete');
    }
}
