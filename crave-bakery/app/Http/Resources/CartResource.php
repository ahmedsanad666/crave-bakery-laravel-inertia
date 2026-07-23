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
        ];
    }
}
