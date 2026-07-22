<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'promo_code' => $this->promo_code,
            'delivery_fee' => (float) $this->delivery_fee,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'delivery_method' => $this->delivery_method,
            'delivery_notes' => $this->delivery_notes,
            'estimated_delivery_at' => $this->estimated_delivery_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'items_count' => (int) (
                $this->order_items_count
                ?? ($this->relationLoaded('orderItems') ? $this->orderItems->count() : 0)
            ),
            'customer_orders_count' => (int) ($this->customer_orders_count ?? 0),
            'customer_ltv' => (float) ($this->customer_ltv ?? 0),
            'customer' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
                    'phone' => $this->user->phone ?? null,
                ],
            ),
            'items' => OrderItemResource::collection(
                $this->whenLoaded('orderItems'),
            ),
        ];
    }
}
