<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CartService $cartService */
        $cartService = app(CartService::class);
        $unitPrice = $cartService->unitPrice($this->resource);
        $product = $this->product;
        $isOnSale = (bool) ($product?->is_on_sale);
        $regularUnitPrice = $product ? (float) $product->regular_price : $unitPrice;

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'selected_attributes' => $this->selected_attributes ?? [],
            'unit_price' => $unitPrice,
            'regular_unit_price' => $regularUnitPrice,
            'is_on_sale' => $isOnSale && $regularUnitPrice > $unitPrice,
            'line_total' => $cartService->lineTotal($this->resource),
            'regular_line_total' => round($regularUnitPrice * (int) $this->quantity, 2),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'thumbnail' => Product::toPublicUrl(
                        $this->product->thumbnail ?? $this->product->og_image,
                    ),
                    'category' => $this->product->relationLoaded('categories')
                        ? $this->product->categories->first()?->name
                        : null,
                    'stock_status' => $this->product->stock_status,
                    'stock_quantity' => (int) $this->product->stock_quantity,
                    'allow_backorders' => (bool) $this->product->allow_backorders,
                    'regular_price' => (float) $this->product->regular_price,
                    'sale_price' => $this->product->sale_price !== null
                        ? (float) $this->product->sale_price
                        : null,
                    'current_price' => (float) $this->product->current_price,
                    'is_on_sale' => (bool) $this->product->is_on_sale,
                ];
            }),
        ];
    }
}
