<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * @param  list<array{id: int|null, url: string}>  $gallery
     * @param  list<array<string, mixed>>  $attributeGroups
     */
    public function __construct(
        $resource,
        private readonly array $gallery = [],
        private readonly array $attributeGroups = [],
        private readonly bool $isFavourited = false,
    ) {
        parent::__construct($resource);
    }

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
            'sku' => $this->sku,
            'regular_price' => (float) $this->regular_price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'current_price' => (float) $this->current_price,
            'is_on_sale' => (bool) $this->is_on_sale,
            'thumbnail' => Product::toPublicUrl($this->thumbnail ?? $this->og_image),
            'gallery' => $this->gallery,
            'average_rating' => round((float) ($this->average_rating ?? 0), 1),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'is_featured' => (bool) $this->is_featured,
            'stock_status' => $this->stock_status,
            'stock_quantity' => (int) $this->stock_quantity,
            'allow_backorders' => (bool) $this->allow_backorders,
            'is_low_stock' => (bool) $this->is_low_stock,
            'badge' => $this->resolveBadge(),
            'attributes' => $this->attributeGroups,
            'categories' => $this->whenLoaded(
                'categories',
                fn () => $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->values(),
            ),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords ?? [],
            'og_image' => Product::toPublicUrl($this->og_image),
            'canonical_url' => $this->canonical_url,
            'is_favourited' => $this->isFavourited,
        ];
    }

    private function resolveBadge(): ?string
    {
        if ($this->is_new) {
            return 'New';
        }

        if ($this->is_featured) {
            return 'Best Seller';
        }

        if ($this->is_on_sale && $this->regular_price > 0) {
            $discount = (int) round((1 - ($this->sale_price / $this->regular_price)) * 100);

            return "{$discount}% Off";
        }

        return null;
    }
}
