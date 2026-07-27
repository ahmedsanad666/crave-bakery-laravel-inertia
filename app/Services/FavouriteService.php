<?php

namespace App\Services;

use App\Models\Favourite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavouriteService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 12), 48));
        $sort = $filters['sort'] ?? 'newest';
        $search = $filters['search'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $query = Favourite::query()
            ->where('favourites.user_id', $user->id)
            ->with([
                'product.categories',
            ])
            ->whereHas('product', function ($productQuery) use ($search, $categoryId) {
                $productQuery->published()
                    ->search($search)
                    ->inCategory($categoryId);
            });

        $effectivePrice = 'COALESCE(products.sale_price, products.regular_price)';

        match ($sort) {
            'price_asc' => $query
                ->join('products', 'products.id', '=', 'favourites.product_id')
                ->orderByRaw("{$effectivePrice} asc")
                ->select('favourites.*'),
            'price_desc' => $query
                ->join('products', 'products.id', '=', 'favourites.product_id')
                ->orderByRaw("{$effectivePrice} desc")
                ->select('favourites.*'),
            'name' => $query
                ->join('products', 'products.id', '=', 'favourites.product_id')
                ->orderBy('products.name')
                ->select('favourites.*'),
            default => $query->latest('favourites.created_at'),
        };

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{favourited: bool}
     */
    public function toggle(User $user, Product $product): array
    {
        $existing = Favourite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['favourited' => false];
        }

        Favourite::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return ['favourited' => true];
    }

    public function clear(User $user): void
    {
        Favourite::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * @return list<int>
     */
    public function favouritedProductIds(User $user): array
    {
        return Favourite::query()
            ->where('user_id', $user->id)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
