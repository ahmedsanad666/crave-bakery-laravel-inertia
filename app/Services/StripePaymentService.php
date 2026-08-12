<?php

namespace App\Services;

use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class StripePaymentService
{
    public const SESSION_KEY = 'checkout.pending';

    private const CACHE_TTL_SECONDS = 7200;

    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    /**
     * Store checkout form data in session without touching cart/stock.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $totals
     * @return array<string, mixed>
     */
    public function beginPendingCheckout(User $user, array $validated, array $totals): array
    {
        $payload = [
            'user_id' => $user->id,
            'checkout_token' => (string) Str::uuid(),
            'validated' => $validated,
            'quoted_total' => round((float) $totals['total'], 2),
            'payment_intent_id' => null,
        ];
        // dd($payload);

        session([self::SESSION_KEY => $payload]);

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPendingCheckout(?User $user = null): ?array
    {
        $pending = session(self::SESSION_KEY);

        if (! is_array($pending)) {
            return null;
        }

        if ($user && (int) ($pending['user_id'] ?? 0) !== $user->id) {
            return null;
        }

        return $pending;
    }

    public function clearPendingCheckout(?User $user = null, ?string $paymentIntentId = null): void
    {
        $pending = $this->getPendingCheckout($user);
        $piId = $paymentIntentId ?: ($pending['payment_intent_id'] ?? null);
        $token = $pending['checkout_token'] ?? null;
        $userId = $user?->id ?? ($pending['user_id'] ?? null);

        session()->forget(self::SESSION_KEY);

        if ($userId && $piId) {
            Cache::forget($this->cacheKeyForIntent((int) $userId, (string) $piId));
        }

        if ($token) {
            Cache::forget($this->cacheKeyForToken((string) $token));
        }
    }

    /**
     * Create or reuse a PaymentIntent from the pending checkout cart totals.
     *
     * @return array{clientSecret: string}
     */
    public function createPaymentIntent(User $user): array
    {
        $pending = $this->getPendingCheckout($user);

        if (! $pending) {
            throw ValidationException::withMessages([
                'payment' => 'Your checkout session expired. Please return to checkout and try again.',
            ]);
        }

        $quote = $this->orderService->quote($user, [
            'delivery_method' => $pending['validated']['delivery_method'] ?? 'standard',
            'promo_code' => $pending['validated']['promo_code'] ?? null,
        ]);

        if (($quote['cart']['item_count'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $liveTotal = round((float) $quote['totals']['total'], 2);
        $quotedTotal = round((float) ($pending['quoted_total'] ?? 0), 2);

        if (abs($liveTotal - $quotedTotal) > 0.01) {
            $pending['quoted_total'] = $liveTotal;
            $pending['validated']['delivery_method'] = $quote['totals']['delivery_method'] ?? $pending['validated']['delivery_method'];
            session([self::SESSION_KEY => $pending]);
        }

        try {
            $user->createOrGetStripeCustomer();

            if (! empty($pending['payment_intent_id'])) {
                $existing = $user->stripe()->paymentIntents->retrieve($pending['payment_intent_id']);

                if (in_array($existing->status, ['succeeded', 'processing'], true)) {
                    throw ValidationException::withMessages([
                        'payment_status' => 'This payment is already in progress or complete.',
                    ]);
                }

                $expectedAmount = (int) round($liveTotal * 100);

                if (
                    $existing->status !== 'canceled'
                    && filled($existing->client_secret)
                    && (int) $existing->amount === $expectedAmount
                ) {
                    $this->persistPendingPayload($user, $pending, $existing->id);

                    return ['clientSecret' => $existing->client_secret];
                }
            }

            $amount = (int) round($liveTotal * 100);

            $payment = $user->pay($amount, [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'checkout_token' => (string) $pending['checkout_token'],
                ],
            ]);

            $pending['payment_intent_id'] = $payment->id;
            $pending['quoted_total'] = $liveTotal;
            $this->persistPendingPayload($user, $pending, $payment->id);

            return ['clientSecret' => $payment->client_secret];
        } catch (ValidationException $e) {
            throw $e;
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'payment' => 'Unable to initialise payment. Please try again.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe PaymentIntent creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'payment' => 'Unable to initialise payment. Please try again.',
            ]);
        }
    }

    /**
     * Verify PaymentIntent and create the order only after success.
     */
    public function confirmPayment(string $paymentIntentId, User $user): Order
    {
        return $this->fulfillStripePayment($user, $paymentIntentId, sendEmail: true);
    }

    /**
     * Idempotent fulfill used by confirm + webhook.
     */
    public function fulfillStripePayment(User $user, string $paymentIntentId, bool $sendEmail = true): Order
    {
        $existing = Order::query()
            ->where('transaction_id', $paymentIntentId)
            ->first();

        if ($existing) {
            if ($existing->user_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }

            $order = $this->markOrderPaid($existing, $paymentIntentId, sendEmail: $sendEmail);
            $this->clearPendingCheckout($user, $paymentIntentId);

            return $order;
        }

        try {
            $paymentIntent = $user->stripe()->paymentIntents->retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment confirmation failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);

            throw ValidationException::withMessages([
                'payment' => 'Something went wrong verifying your payment. Please contact support.',
            ]);
        }

        if ($paymentIntent->status !== 'succeeded') {
            throw ValidationException::withMessages([
                'payment' => 'Payment was not successful. Please try again.',
            ]);
        }

        $metaUserId = (int) ($paymentIntent->metadata->user_id ?? 0);
        if ($metaUserId && $metaUserId !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $pending = $this->resolvePendingPayload($user, $paymentIntentId, $paymentIntent->metadata->checkout_token ?? null);

        if (! $pending || empty($pending['validated'])) {
            throw ValidationException::withMessages([
                'payment' => 'Your checkout session expired after payment. Please contact support with your payment reference.',
            ]);
        }

        $existingBefore = Order::query()
            ->where('transaction_id', $paymentIntentId)
            ->first();
        $alreadyPaid = $existingBefore && $existingBefore->payment_status === 'paid';

        $order = $this->orderService->createPaidStripeOrderFromCart(
            $user,
            $pending['validated'],
            $paymentIntentId,
        );

        $this->clearPendingCheckout($user, $paymentIntentId);

        if ($sendEmail && ! $alreadyPaid && filled($order->email)) {
            Mail::to($order->email)->send(new OrderConfirmed($order->fresh(['orderItems.product'])));
        }

        return $order->fresh(['orderItems.product']);
    }

    /**
     * @param  array<string, mixed>  $paymentIntent
     */
    public function handlePaymentSucceeded(array $paymentIntent): void
    {
        $paymentIntentId = $paymentIntent['id'] ?? null;

        if (! is_string($paymentIntentId) || $paymentIntentId === '') {
            return;
        }

        $order = Order::query()->where('transaction_id', $paymentIntentId)->first();

        if ($order) {
            $this->markOrderPaid($order, $paymentIntentId, sendEmail: true);
            $this->clearPendingCheckout($order->user, $paymentIntentId);

            return;
        }

        $userId = (int) ($paymentIntent['metadata']['user_id'] ?? 0);
        $user = $userId ? User::query()->find($userId) : null;

        if (! $user) {
            Log::warning('Webhook: payment_intent.succeeded — no matching user or order', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        try {
            $this->fulfillStripePayment($user, $paymentIntentId, sendEmail: true);
        } catch (\Throwable $e) {
            Log::error('Webhook: failed to fulfill Stripe payment', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $paymentIntent
     */
    public function handlePaymentFailed(array $paymentIntent): void
    {
        $paymentIntentId = $paymentIntent['id'] ?? null;

        if (! is_string($paymentIntentId)) {
            return;
        }

        $order = Order::query()->where('transaction_id', $paymentIntentId)->first();

        if (! $order || $order->payment_status === 'paid') {
            Log::info('Payment failed for pending Stripe checkout', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        $order->update([
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    public function refundPayment(Order $order): void
    {
        if ($order->payment_method !== 'stripe' || blank($order->transaction_id)) {
            return;
        }

        $user = $order->user;

        if (! $user) {
            throw ValidationException::withMessages([
                'payment_status' => 'Unable to refund: order has no customer.',
            ]);
        }

        try {
            $user->refund($order->transaction_id);
        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id,
            ]);

            throw ValidationException::withMessages([
                'payment_status' => 'Stripe refund failed: '.$e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe refund failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id,
            ]);

            throw ValidationException::withMessages([
                'payment_status' => 'Stripe refund failed: '.$e->getMessage(),
            ]);
        }
    }

    public function markOrderPaid(Order $order, string $paymentIntentId, bool $sendEmail = false): Order
    {
        $wasUnpaid = $order->payment_status !== 'paid';

        if ($wasUnpaid) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'paid_at' => now(),
                'transaction_id' => $paymentIntentId,
            ]);
        }

        if ($sendEmail && $wasUnpaid && filled($order->email)) {
            Mail::to($order->email)->send(new OrderConfirmed($order->fresh(['orderItems.product'])));
        }

        return $order->fresh(['orderItems.product']);
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    private function persistPendingPayload(User $user, array $pending, string $paymentIntentId): void
    {
        $pending['payment_intent_id'] = $paymentIntentId;
        $pending['user_id'] = $user->id;

        session([self::SESSION_KEY => $pending]);

        Cache::put(
            $this->cacheKeyForIntent($user->id, $paymentIntentId),
            $pending,
            self::CACHE_TTL_SECONDS,
        );

        if (! empty($pending['checkout_token'])) {
            Cache::put(
                $this->cacheKeyForToken((string) $pending['checkout_token']),
                $pending,
                self::CACHE_TTL_SECONDS,
            );
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolvePendingPayload(User $user, string $paymentIntentId, ?string $checkoutToken): ?array
    {
        $pending = $this->getPendingCheckout($user);

        if (is_array($pending) && ! empty($pending['validated'])) {
            return $pending;
        }

        $fromIntent = Cache::get($this->cacheKeyForIntent($user->id, $paymentIntentId));

        if (is_array($fromIntent) && ! empty($fromIntent['validated'])) {
            return $fromIntent;
        }

        if ($checkoutToken) {
            $fromToken = Cache::get($this->cacheKeyForToken($checkoutToken));

            if (is_array($fromToken) && ! empty($fromToken['validated'])) {
                return $fromToken;
            }
        }

        return null;
    }

    private function cacheKeyForIntent(int $userId, string $paymentIntentId): string
    {
        return "checkout.pending.{$userId}.{$paymentIntentId}";
    }

    private function cacheKeyForToken(string $token): string
    {
        return "checkout.pending.token.{$token}";
    }
}
