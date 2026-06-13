<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isFavourited = false;

        if ($request->user()) {
            $isFavourited = $this->favourites()
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'regular_price' => (float) $this->regular_price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'thumbnail' => $this->og_image,
            'average_rating' => 0,
            'reviews_count' => 0,
            'is_featured' => $this->is_featured,
            'stock_status' => $this->stock_status,
            'badge' => $this->resolveBadge(),
            'categories' => $this->whenLoaded(
                'categories',
                fn () => $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])->values(),
            ),
            'is_favourited' => $isFavourited,
        ];
    }

    private function resolveBadge(): ?string
    {
        if ($this->is_on_sale && $this->regular_price > 0) {
            $discount = (int) round((1 - ($this->sale_price / $this->regular_price)) * 100);

            return "{$discount}% Off";
        }

        if ($this->is_featured) {
            return 'Best Seller';
        }

        return null;
    }
}
