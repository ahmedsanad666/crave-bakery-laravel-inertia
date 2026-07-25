<?php

namespace App\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const FREE_DELIVERY_THRESHOLD = 30.0;

    private const STANDARD_DELIVERY_FEE = 4.99;

    private const EXPRESS_DELIVERY_FEE = 9.99;

    private const TAX_RATE = 0.08;

    public function __construct(
        private readonly CartService $cartService,
        private readonly PromoCodeService $promoCodeService,
    ) {
    }

    /**
     * @return array{
     *     total: int,
     *     pending: int,
     *     processing: int,
     *     shipped: int,
     *     delivered: int,
     *     cancelled: int
     * }
     */
    public function stats(): array
    {
        return [
            'total' => Order::query()->count(),
            'pending' => Order::query()->where('status', 'pending')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'shipped' => Order::query()->where('status', 'shipped')->count(),
            'delivered' => Order::query()->where('status', 'delivered')->count(),
            'cancelled' => Order::query()->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Order::query()
            ->with([
                'user:id,name,email,avatar',
                'orderItems.product:id,name,thumbnail,sku',
            ])
            ->withCount('orderItems')
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->paymentStatus($filters['payment_status'] ?? null)
            ->paymentMethod($filters['payment_method'] ?? null)
            ->deliveryMethod($filters['delivery_method'] ?? null)
            ->dateFrom($filters['date_from'] ?? null)
            ->dateTo($filters['date_to'] ?? null)
            ->amountMin($filters['amount_min'] ?? null)
            ->amountMax($filters['amount_max'] ?? null)
            ->ordered()
            ->paginate(max(1, min($perPage, 100)))
            ->withQueryString();
    }

    public function findForAdmin(Order $order): Order
    {
        $order->load([
            'user:id,name,email,avatar,phone',
            'orderItems.product:id,name,thumbnail,sku',
        ]);

        if ($order->user_id) {
            $stats = Order::query()
                ->where('user_id', $order->user_id)
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', 'paid')
                ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as ltv')
                ->first();

            $order->setAttribute('customer_orders_count', (int) ($stats->orders_count ?? 0));
            $order->setAttribute('customer_ltv', (float) ($stats->ltv ?? 0));
        } else {
            $order->setAttribute('customer_orders_count', 0);
            $order->setAttribute('customer_ltv', 0.0);
        }

        return $order;
    }

    /**
     * @param  array{status?: string|null, search?: string|null, per_page?: int}  $filters
     */
    public function paginateForCustomer(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        return Order::query()
            ->where('user_id', $user->id)
            ->with(['orderItems.product:id,name,slug,thumbnail,sku'])
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->ordered()
            ->paginate(max(1, min($perPage, 50)))
            ->withQueryString();
    }

    public function findForCustomer(User $user, Order $order): Order
    {
        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load(['orderItems.product:id,name,slug,thumbnail,sku']);

        return $order;
    }

    /**
     * @param  array{delivery_method?: string, promo_code?: string|null}  $input
     * @return array{cart: array<string, mixed>, totals: array<string, mixed>}
     */
    public function quote(User $user, array $input = []): array
    {
        $cartPayload = $this->cartService->getCartPayloadForUser($user);
        // dd($cartPayload['items'][0]->toArray());
        $deliveryMethod = ($input['delivery_method'] ?? 'standard') === 'express'
            ? 'express'
            : 'standard';

        $totals = $this->computeTotals(
            (float) $cartPayload['subtotal'],
            $deliveryMethod,
            $input['promo_code'] ?? null,
        );
        // dd($totals);

        return [
            'cart' => $cartPayload,
            'totals' => $totals,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createFromCart(User $user, array $validated): Order
    {
        return DB::transaction(function () use ($user, $validated) {
            $address = Address::query()
                ->where('user_id', $user->id)
                ->whereKey($validated['address_id'])
                ->first();

            if (! $address) {
                throw ValidationException::withMessages([
                    'address_id' => 'Please select a valid delivery address.',
                ]);
            }

            $cartPayload = $this->cartService->getCartPayloadForUser($user);
            /** @var \Illuminate\Support\Collection<int, CartItem> $items */
            $items = $cartPayload['items'];

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            foreach ($items as $item) {
                $product = $item->product;

                if (! $product || ! $this->cartService->isProductPurchasable($product, $item->quantity)) {
                    throw ValidationException::withMessages([
                        'cart' => sprintf(
                            '"%s" is no longer available in the requested quantity.',
                            $product?->name ?? 'A product',
                        ),
                    ]);
                }
            }

            $deliveryMethod = $validated['delivery_method'] === 'express' ? 'express' : 'standard';
            $totals = $this->computeTotals(
                (float) $cartPayload['subtotal'],
                $deliveryMethod,
                $validated['promo_code'] ?? null,
            );

            $promo = null;
            if (filled($totals['promo_code'])) {
                $promo = $this->promoCodeService->findValid(
                    (string) $totals['promo_code'],
                    (float) $cartPayload['subtotal'],
                );
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'transaction_id' => null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'promo_code' => $totals['promo_code'],
                'delivery_fee' => $totals['delivery_fee'],
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'email' => $validated['email'],
                'phone' => $address->phone,
                'address_line1' => $address->address_line1,
                'address_line2' => $address->address_line2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'delivery_method' => $deliveryMethod,
                'delivery_notes' => $validated['delivery_notes'] ?? null,
                'estimated_delivery_at' => $deliveryMethod === 'express'
                    ? now()->addDay()
                    : now()->addDays(2),
                'paid_at' => null,
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $item->product;
                $unitPrice = $this->cartService->unitPrice($item);
                $lineTotal = $this->cartService->lineTotal($item);

                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'selected_attributes' => $item->selected_attributes ?? [],
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $this->decrementStock($product, $item->quantity);
            }

            if ($promo instanceof PromoCode) {
                $promo->increment('used_count');
            }

            if (isset($cartPayload['cart']) && $cartPayload['cart']) {
                $this->cartService->clearCart($cartPayload['cart']);
            }

            return $order->fresh(['orderItems.product']);
        });
    }

    /**
     * Create an order from the cart and mark it paid for a successful Stripe PaymentIntent.
     * Cart / stock / promo side effects only run here (not before payment).
     *
     * @param  array<string, mixed>  $validated
     */
    public function createPaidStripeOrderFromCart(
        User $user,
        array $validated,
        string $paymentIntentId,
    ): Order {
        return DB::transaction(function () use ($user, $validated, $paymentIntentId) {
            $existing = Order::query()
                ->where('transaction_id', $paymentIntentId)
                ->first();

            if ($existing) {
                if ($existing->user_id !== $user->id) {
                    abort(403, 'Unauthorized.');
                }

                if ($existing->payment_status !== 'paid') {
                    $existing->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'paid_at' => $existing->paid_at ?? now(),
                        'payment_method' => 'stripe',
                    ]);
                }

                return $existing->fresh(['orderItems.product']);
            }

            $validated['payment_method'] = 'stripe';
            $order = $this->createFromCart($user, $validated);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'paid_at' => now(),
                'transaction_id' => $paymentIntentId,
            ]);

            return $order->fresh(['orderItems.product']);
        });
    }

    public function updateStatus(Order $order, string $status, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $status, $note) {
            $order->status = $status;

            if ($status === 'delivered' && $order->delivered_at === null) {
                $order->delivered_at = now();
            }

            // COD cash collected on delivery — mark as paid for invoices/refunds
            if (
                $status === 'delivered'
                && $order->payment_method === 'cod'
                && $order->payment_status === 'pending'
            ) {
                $order->payment_status = 'paid';
                $order->paid_at = now();
            }

            if ($status === 'cancelled') {
                $order->delivered_at = null;
            }

            if (filled($note)) {
                $notes = $order->notes ?? [];
                $notes[] = [
                    'body' => $note,
                    'created_at' => now()->toIso8601String(),
                    'type' => 'status_change',
                    'status' => $status,
                ];
                $order->notes = $notes;
            }

            $order->save();

            return $order->fresh([
                'user:id,name,email,avatar,phone',
                'orderItems.product:id,name,thumbnail,sku',
            ]);
        });
    }

    public function refund(Order $order, ?string $reason = null): Order
    {
        if ($order->payment_status === 'refunded') {
            throw ValidationException::withMessages([
                'payment_status' => 'This order has already been refunded.',
            ]);
        }

        if ($order->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'payment_status' => 'Only paid orders can be refunded.',
            ]);
        }

        if ($order->payment_method === 'stripe') {
            app(StripePaymentService::class)->refundPayment($order);
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->payment_status = 'refunded';

            if (filled($reason)) {
                $notes = $order->notes ?? [];
                $notes[] = [
                    'body' => $reason,
                    'created_at' => now()->toIso8601String(),
                    'type' => 'refund',
                ];
                $order->notes = $notes;
            }

            $order->save();

            return $order->fresh([
                'user:id,name,email,avatar,phone',
                'orderItems.product:id,name,thumbnail,sku',
            ]);
        });
    }

    /**
     * @return array{
     *     subtotal: float,
     *     discount_amount: float,
     *     promo_code: string|null,
     *     delivery_fee: float,
     *     tax_amount: float,
     *     total: float,
     *     delivery_method: string
     * }
     */
    private function computeTotals(float $subtotal, string $deliveryMethod, ?string $promoCode): array
    {
        $subtotal = round(max(0, $subtotal), 2);
        $discountAmount = 0.0;
        $resolvedPromoCode = null;

        if (filled($promoCode)) {
            $promo = $this->promoCodeService->findValid($promoCode, $subtotal);
            $discountAmount = $this->promoCodeService->discountAmount($promo, $subtotal);
            $resolvedPromoCode = $promo->code;
        }

        $taxable = max(0, $subtotal - $discountAmount);
        $deliveryFee = $this->deliveryFee($taxable, $deliveryMethod);
        $taxAmount = round($taxable * self::TAX_RATE, 2);
        $total = round($taxable + $deliveryFee + $taxAmount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'promo_code' => $resolvedPromoCode,
            'delivery_fee' => $deliveryFee,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'delivery_method' => $deliveryMethod,
        ];
    }

    private function deliveryFee(float $amountAfterDiscount, string $deliveryMethod): float
    {
        if ($deliveryMethod === 'express') {
            return self::EXPRESS_DELIVERY_FEE;
        }

        if ($amountAfterDiscount >= self::FREE_DELIVERY_THRESHOLD) {
            return 0.0;
        }

        return self::STANDARD_DELIVERY_FEE;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'CRV-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    private function decrementStock(Product $product, int $quantity): void
    {
        $product->stock_quantity = max(0, $product->stock_quantity - $quantity);

        if ($product->stock_quantity <= 0 && ! $product->allow_backorders) {
            $product->stock_status = 'out_of_stock';
        } elseif ($product->stock_quantity <= 0 && $product->allow_backorders) {
            $product->stock_status = 'on_backorder';
        }

        $product->save();
    }
}
