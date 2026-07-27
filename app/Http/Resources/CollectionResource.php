<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
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
                fn () => $this->products->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'thumbnail' => Product::toPublicUrl($product->thumbnail ?? $product->og_image),
                ])->values(),
            ),
        ];
    }
}
