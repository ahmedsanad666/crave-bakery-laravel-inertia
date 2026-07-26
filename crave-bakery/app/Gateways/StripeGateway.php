<?php

namespace App\Gateways;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\PaymentGatewayModel;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class StripeGateway implements PaymentGateway
{
    private PaymentGatewayModel $gateway;

    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {
        $this->gateway = PaymentGatewayModel::query()
            ->where('name', 'stripe')
            ->firstOrFail();

        $this->applyCashierCredentials();
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{clientSecret: string}
     */
    public function createIntent(User $user, array $context = []): array
    {
        $this->assertConfigured();

        return $this->stripePaymentService->createPaymentIntent($user);
    }

    public function verify(string $reference): bool
    {
        $this->assertConfigured();

        try {
            $user = auth()->user();

            if (! $user instanceof User) {
                return false;
            }

            $paymentIntent = $user->stripe()->paymentIntents->retrieve($reference);

            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            Log::error('Stripe verify failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function refund(Order $order, ?float $amount = null): bool
    {
        $this->assertConfigured();
        $this->stripePaymentService->refundPayment($order);

        return true;
    }

    public function publishableKey(): string
    {
        return (string) ($this->gateway->getConfig('key')
            ?: config('cashier.key')
            ?: config('services.stripe.key')
            ?: '');
    }

    public function isConfigured(): bool
    {
        return filled($this->gateway->getConfig('secret'))
            && filled($this->publishableKey());
    }

    private function applyCashierCredentials(): void
    {
        $secret = $this->gateway->getConfig('secret');
        $key = $this->gateway->getConfig('key');
        $webhookSecret = $this->gateway->getConfig('webhook_secret');

        if (filled($key)) {
            config(['cashier.key' => $key]);
        }

        if (filled($secret)) {
            config(['cashier.secret' => $secret]);
        }

        if (filled($webhookSecret)) {
            config(['cashier.webhook.secret' => $webhookSecret]);
        }
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'payment' => 'Card payments are not configured. Please contact support.',
            ]);
        }
    }
}
