<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
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

    public function updateStatus(Order $order, string $status, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $status, $note) {
            $order->status = $status;

            if ($status === 'delivered' && $order->delivered_at === null) {
                $order->delivered_at = now();
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
}
