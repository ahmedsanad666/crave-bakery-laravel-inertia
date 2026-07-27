<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'privacy' => $this->privacy,
            'products_count' => (int) ($this->products_count ?? $this->products?->count() ?? 0),
            'products' => $this->whenLoaded(
                'products',
                fn () => $this->products->map(function (Product $product) use ($request) {
                    $payload = (new ProductResource($product))->resolve($request);

                    return [
                        ...$payload,
                        'added_at' => $product->pivot?->created_at?->toIso8601String(),
                    ];
                })->values(),
            ),
        ];
    }
}
