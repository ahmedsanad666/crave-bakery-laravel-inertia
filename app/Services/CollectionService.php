<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection as SupportCollection;

class CollectionService
{
    /**
     * @return SupportCollection<int, Collection>
     */
    public function listForUser(User $user): SupportCollection
    {
        $collections = $user->collections()
            ->withCount('products')
            ->with(['products' => function ($query) {
                $query->select('products.id', 'products.name', 'products.slug', 'products.thumbnail', 'products.og_image')
                    ->latest('collection_product.created_at');
            }])
            ->latest()
            ->get();

        $collections->each(function (Collection $collection) {
            $collection->setRelation('products', $collection->products->take(4)->values());
        });

        return $collections;
    }

    public function findForUser(User $user, Collection $collection): Collection
    {
        if ($collection->user_id !== $user->id) {
            abort(404);
        }

        $approvedReviews = fn ($query) => $query->where('status', 'approved');

        $collection->loadCount('products');
        $collection->load([
            'products' => function ($query) use ($approvedReviews) {
                $query->with('categories')
                    ->withAvg(['reviews as average_rating' => $approvedReviews], 'rating')
                    ->withCount(['reviews as reviews_count' => $approvedReviews])
                    ->latest('collection_product.created_at');
            },
        ]);

        return $collection;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Collection
    {
        return $user->collections()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'privacy' => $data['privacy'] ?? 'private',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Collection $collection, array $data): Collection
    {
        $collection->update([
            'name' => $data['name'] ?? $collection->name,
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : $collection->description,
            'privacy' => $data['privacy'] ?? $collection->privacy,
        ]);

        return $collection->fresh();
    }

    public function delete(Collection $collection): void
    {
        $collection->delete();
    }

    public function attachProduct(Collection $collection, Product $product): void
    {
        $collection->products()->syncWithoutDetaching([$product->id]);
    }

    public function detachProduct(Collection $collection, Product $product): void
    {
        $collection->products()->detach($product->id);
    }
}
