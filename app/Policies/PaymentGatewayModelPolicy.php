<?php

namespace App\Policies;

use App\Models\PaymentGatewayModel;
use App\Models\User;

class PaymentGatewayModelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('site_settings', 'view');
    }

    public function update(User $user, PaymentGatewayModel $paymentGatewayModel): bool
    {
        return $user->hasPermission('site_settings', 'edit');
    }
}
