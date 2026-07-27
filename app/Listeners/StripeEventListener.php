<?php

namespace App\Listeners;

use App\Services\StripePaymentService;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {
    }

    public function handle(WebhookReceived $event): void
    {
        $type = $event->payload['type'] ?? null;
        $object = $event->payload['data']['object'] ?? null;

        if (! is_array($object)) {
            return;
        }

        match ($type) {
            'payment_intent.succeeded' => $this->stripePaymentService->handlePaymentSucceeded($object),
            'payment_intent.payment_failed' => $this->stripePaymentService->handlePaymentFailed($object),
            default => null,
        };
    }
}
