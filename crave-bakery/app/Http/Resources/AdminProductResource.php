<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'regular_price' => (float) $this->regular_price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'cost_price' => $this->cost_price !== null ? (float) $this->cost_price : null,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'stock_quantity' => (int) $this->stock_quantity,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'allow_backorders' => (bool) $this->allow_backorders,
            'stock_status' => $this->stock_status,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'thumbnail' => Product::toPublicUrl($this->thumbnail),
            'published_at' => $this->published_at?->toIso8601String(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords ?? [],
            'og_image' => Product::toPublicUrl($this->og_image),
            'canonical_url' => $this->canonical_url,
            'current_price' => (float) $this->current_price,
            'is_on_sale' => (bool) $this->is_on_sale,
            'is_low_stock' => (bool) $this->is_low_stock,
            'images' => $this->when(
                $this->relationLoaded('images'),
                fn () => $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => Product::toPublicUrl($image->path),
                    'sort_order' => (int) $image->sort_order,
                ])->values()->all(),
            ),
            'categories' => $this->when(
                $this->relationLoaded('categories'),
                fn () => $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])->values()->all(),
            ),
            'category_ids' => $this->when(
                $this->relationLoaded('categories'),
                fn () => $this->categories->pluck('id')->values()->all(),
            ),
            'attribute_value_ids' => $this->when(
                $this->relationLoaded('attributeValues'),
                fn () => $this->attributeValues->pluck('id')->values()->all(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
