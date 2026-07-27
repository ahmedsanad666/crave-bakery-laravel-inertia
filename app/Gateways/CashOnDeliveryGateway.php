<?php

namespace App\Gateways;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\PaymentGatewayModel;
use App\Models\User;

class CashOnDeliveryGateway implements PaymentGateway
{
    private PaymentGatewayModel $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGatewayModel::query()
            ->where('name', 'cod')
            ->firstOrFail();
    }

    /**
     * COD has no external API — checkout creates the order directly.
     *
     * @param  array<string, mixed>  $context
     * @return array{method: string, instructions: string|null}
     */
    public function createIntent(User $user, array $context = []): array
    {
        return [
            'method' => 'cod',
            'instructions' => $this->gateway->instructions,
        ];
    }

    /**
     * COD is verified when the delivery driver collects payment.
     */
    public function verify(string $reference): bool
    {
        return true;
    }

    /**
     * COD refunds are handled offline — no API call needed.
     */
    public function refund(Order $order, ?float $amount = null): bool
    {
        return true;
    }
}
