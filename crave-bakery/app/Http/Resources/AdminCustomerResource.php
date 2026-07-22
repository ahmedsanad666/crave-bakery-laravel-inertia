<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ordersCount = (int) (
            $this->orders_count
            ?? $this->paid_orders_count
            ?? 0
        );

        $totalSpent = (float) (
            $this->total_spent
            ?? $this->paid_orders_sum_total
            ?? 0
        );

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'status' => $this->status ?? 'active',
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'orders_count' => $ordersCount,
            'total_spent' => round($totalSpent, 2),
            'reviews_count' => $this->when(
                isset($this->reviews_count),
                fn () => (int) $this->reviews_count,
            ),
            'avg_order_value' => $this->when(
                array_key_exists('avg_order_value', $this->getAttributes())
                    || isset($this->avg_order_value),
                fn () => $this->avg_order_value !== null
                    ? round((float) $this->avg_order_value, 2)
                    : null,
            ),
            'last_order_at' => $this->when(
                array_key_exists('last_order_at', $this->getAttributes())
                    || isset($this->last_order_at),
                fn () => $this->last_order_at
                    ? (is_string($this->last_order_at)
                        ? $this->last_order_at
                        : $this->last_order_at->toIso8601String())
                    : null,
            ),
            'addresses' => $this->when(
                $this->relationLoaded('addresses'),
                fn () => $this->addresses->map(fn ($address) => [
                    'id' => $address->id,
                    'label' => $address->label,
                    'first_name' => $address->first_name,
                    'last_name' => $address->last_name,
                    'phone' => $address->phone,
                    'address_line1' => $address->address_line1,
                    'address_line2' => $address->address_line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'is_default' => (bool) $address->is_default,
                ])->values()->all(),
            ),
            'orders' => $this->when(
                $this->relationLoaded('orders'),
                fn () => $this->orders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => (float) $order->total,
                    'items_count' => (int) ($order->order_items_count ?? 0),
                    'created_at' => $order->created_at?->toIso8601String(),
                ])->values()->all(),
            ),
        ];
    }
}
