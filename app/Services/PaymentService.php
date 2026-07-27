<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Gateways\CashOnDeliveryGateway;
use App\Gateways\StripeGateway;
use App\Models\PaymentGatewayModel;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Resolve the correct gateway class based on the gateway name.
     *
     * HOW TO ADD A NEW GATEWAY:
     * 1. Create the gateway class in app/Gateways/
     * 2. Add a new case here
     *
     * @param  bool  $requireEnabled  When false (e.g. refunds), allow disabled gateways
     */
    public function resolve(string $gatewayName, bool $requireEnabled = true): PaymentGateway
    {
        $record = $this->find($gatewayName);

        if ($requireEnabled && ! $record->is_enabled) {
            throw ValidationException::withMessages([
                'payment_method' => "Payment method [{$gatewayName}] is currently unavailable.",
            ]);
        }

        return match ($gatewayName) {
            'stripe' => app(StripeGateway::class),
            'cod' => app(CashOnDeliveryGateway::class),
            default => throw new \InvalidArgumentException(
                "Payment gateway [{$gatewayName}] is not supported."
            ),
        };
    }

    /**
     * Get all gateways the admin has enabled (safe for checkout).
     *
     * @return Collection<int, PaymentGatewayModel>
     */
    public function enabledGateways(): Collection
    {
        return PaymentGatewayModel::enabled()->get()->filter(function (PaymentGatewayModel $gateway) {
            if ($gateway->name !== 'stripe') {
                return true;
            }

            // Hide Stripe from checkout if credentials are missing
            return filled($gateway->getConfig('secret'))
                && filled($gateway->getConfig('key') ?: config('cashier.key') ?: config('services.stripe.key'));
        })->values();
    }

    /**
     * @return list<string>
     */
    public function enabledGatewayNames(): array
    {
        return $this->enabledGateways()->pluck('name')->all();
    }

    public function find(string $name): PaymentGatewayModel
    {
        return PaymentGatewayModel::query()->where('name', $name)->firstOrFail();
    }

    public function publishableKey(string $gatewayName = 'stripe'): string
    {
        if ($gatewayName !== 'stripe') {
            return '';
        }

        try {
            $gateway = app(StripeGateway::class);

            return $gateway->publishableKey();
        } catch (\Throwable) {
            return (string) (config('cashier.key') ?: config('services.stripe.key') ?: '');
        }
    }

    /**
     * Apply Stripe DB credentials into Cashier config (for webhooks / early boot).
     */
    public function applyStripeCredentials(): void
    {
        try {
            app(StripeGateway::class);
        } catch (\Throwable) {
            // Table may be empty during migrate; fall back to .env
        }
    }
}
