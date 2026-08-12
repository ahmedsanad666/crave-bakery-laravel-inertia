<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => CartItemResource::collection($this->resource['items'] ?? [])->resolve(),
            'item_count' => (int) ($this->resource['item_count'] ?? 0),
            'subtotal' => (float) ($this->resource['subtotal'] ?? 0),
            'promo_code' => $this->resource['promo_code'] ?? null,
            'discount_amount' => (float) ($this->resource['discount_amount'] ?? 0),
            'total_after_discount' => (float) ($this->resource['total_after_discount']
                ?? ($this->resource['subtotal'] ?? 0)),
        ];
    }
}
