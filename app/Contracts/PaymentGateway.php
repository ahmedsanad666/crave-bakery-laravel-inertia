<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\User;

/**
 * Every payment gateway must implement this interface.
 *
 * HOW TO ADD A NEW GATEWAY:
 * 1. Create app/Gateways/YourGateway.php implementing this interface
 * 2. Add it to PaymentService::resolve()
 * 3. Add a Vue component in resources/js/Components/Public/Gateways/
 * 4. Seed a row in payment_gateways
 */
interface PaymentGateway
{
    /**
     * Create a payment intent / session with the gateway.
     * Stripe uses pending-checkout context (no order yet).
     * COD may be unused — order is created directly on checkout submit.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function createIntent(User $user, array $context = []): array;

    /**
     * Verify that a payment actually succeeded with the gateway.
     * Never trust frontend-reported success alone.
     */
    public function verify(string $reference): bool;

    /**
     * Process a refund for a paid order.
     */
    public function refund(Order $order, ?float $amount = null): bool;
}
