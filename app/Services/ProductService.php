<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Favourite;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {
    }

    /**
     * @return array{total: int, active: int, out_of_stock: int, featured: int}
     */
    public function stats(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'out_of_stock' => Product::where('stock_status', 'out_of_stock')->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Product::query()
            ->with(['categories', 'images'])
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->stockStatus($filters['stock_status'] ?? null)
            ->featured($filters['featured'] ?? null)
            ->inCategory($filters['category_id'] ?? null)
            ->latest()
            ->paginate(max(1, min($perPage, 100)))
            ->withQueryString();
    }

    /**
     * Public shop catalogue: published products only + shop filters/facets.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     products: LengthAwarePaginator,
     *     categoryOptions: list<array{id: int|null, name: string, products_count: int, image: string|null, children: list<array<string, mixed>>}>,
     *     priceBounds: array{min: float, max: float},
     *     filters: array<string, mixed>,
     *     favourited_product_ids: list<int>
     * }
     */
    public function paginateForShop(array $filters = []): array
    {
        $normalized = $this->normalizeShopFilters($filters);
        $perPage = (int) $normalized['per_page'];

        $approvedReviews = fn ($query) => $query->where('status', 'approved');

        $products = Product::query()
            ->published()
            ->with('categories')
            ->withAvg(['reviews as average_rating' => $approvedReviews], 'rating')
            ->withCount(['reviews as reviews_count' => $approvedReviews])
            ->search($normalized['search'])
            ->inCategory($normalized['category_id'])
            ->priceRange($normalized['price_min'], $normalized['price_max'])
            ->availability($normalized['in_stock'], $normalized['out_of_stock'])
            ->minRating($normalized['min_rating'])
            ->sorted($normalized['sort'])
            ->paginate(max(1, min($perPage, 48)))
            ->withQueryString();

        $favouritedIds = [];
        $userId = auth()->id();

        if ($userId) {
            $favouritedIds = Favourite::query()
                ->where('user_id', $userId)
                ->whereIn('product_id', $products->getCollection()->pluck('id'))
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [
            'products' => $products,
            'categoryOptions' => $this->shopCategoryOptions(),
            'priceBounds' => $this->shopPriceBounds(),
            'filters' => $normalized,
            'favourited_product_ids' => $favouritedIds,
        ];
    }

    /**
     * Public product detail: published product + gallery, attributes, related, reviews.
     *
     * @return array{
     *     product: Product,
     *     attributes: list<array<string, mixed>>,
     *     gallery: list<array{id: int|null, url: string}>,
     *     relatedProducts: EloquentCollection<int, Product>,
     *     reviews: array{
     *         average_rating: float,
     *         reviews_count: int,
     *         rating_breakdown: array<int, int>,
     *         items: EloquentCollection<int, Review>
     *     },
     *     is_favourited: bool,
     *     can_review: bool
     * }
     */
    public function findForShop(Product $product): array
    {
        $approvedReviews = fn ($query) => $query->where('status', 'approved');

        $product = Product::query()
            ->published()
            ->whereKey($product->id)
            ->with([
                'categories',
                'images',
                'attributeValues.attribute',
            ])
            ->withAvg(['reviews as average_rating' => $approvedReviews], 'rating')
            ->withCount(['reviews as reviews_count' => $approvedReviews])
            ->firstOrFail();

        $isFavourited = false;
        $canReview = false;
        $user = auth()->user();

        if ($user) {
            $isFavourited = Favourite::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->exists();

            $canReview = $this->reviewService->userCanReview($user, $product);
        }

        return [
            'product' => $product,
            'attributes' => $this->shopProductAttributes($product),
            'gallery' => $this->shopProductGallery($product),
            'relatedProducts' => $this->shopRelatedProducts($product),
            'reviews' => $this->shopProductReviews($product),
            'is_favourited' => $isFavourited,
            'can_review' => $canReview,
        ];
    }

    /**
     * @return list<array{id: int|null, url: string}>
     */
    private function shopProductGallery(Product $product): array
    {
        $gallery = [];
        $seen = [];

        $push = function (?string $path, ?int $id = null) use (&$gallery, &$seen): void {
            $url = Product::toPublicUrl($path);

            if (! $url || isset($seen[$url])) {
                return;
            }

            $seen[$url] = true;
            $gallery[] = [
                'id' => $id,
                'url' => $url,
            ];
        };

        $push($product->thumbnail ?? $product->og_image);

        foreach ($product->images as $image) {
            $push($image->path, (int) $image->id);
        }

        return $gallery;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function shopProductAttributes(Product $product): array
    {
        return $product->attributeValues
            ->groupBy('attribute_id')
            ->map(function (Collection $values) {
                /** @var \App\Models\AttributeValue $first */
                $first = $values->first();
                $attribute = $first->attribute;

                if (! $attribute) {
                    return null;
                }

                $sortedValues = $values
                    ->sortBy([
                        ['sort_order', 'asc'],
                        ['value', 'asc'],
                    ])
                    ->values()
                    ->map(fn ($value) => [
                        'id' => $value->id,
                        'value' => $value->value,
                        'color_swatch' => $value->color_swatch,
                        'sort_order' => (int) $value->sort_order,
                    ])
                    ->all();

                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'display_type' => $attribute->display_type,
                    'sort_order' => (int) $attribute->sort_order,
                    'values' => $sortedValues,
                ];
            })
            ->filter()
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    /**
     * @return EloquentCollection<int, Product>
     */
    private function shopRelatedProducts(Product $product): EloquentCollection
    {
        $categoryIds = $product->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return new EloquentCollection;
        }

        $approvedReviews = fn ($query) => $query->where('status', 'approved');

        return Product::query()
            ->published()
            ->whereKeyNot($product->id)
            ->whereHas(
                'categories',
                fn ($query) => $query->whereIn('categories.id', $categoryIds),
            )
            ->with('categories')
            ->withAvg(['reviews as average_rating' => $approvedReviews], 'rating')
            ->withCount(['reviews as reviews_count' => $approvedReviews])
            ->latest('published_at')
            ->limit(4)
            ->get();
    }

    /**
     * @return array{
     *     average_rating: float,
     *     reviews_count: int,
     *     rating_breakdown: array<int, int>,
     *     items: EloquentCollection<int, Review>
     * }
     */
    private function shopProductReviews(Product $product): array
    {
        $breakdownRows = Review::query()
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as aggregate')
            ->groupBy('rating')
            ->pluck('aggregate', 'rating');

        $ratingBreakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $ratingBreakdown[$star] = (int) ($breakdownRows[$star] ?? 0);
        }

        $items = Review::query()
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->with('user:id,name,avatar')
            ->latest()
            ->limit(10)
            ->get();

        return [
            'average_rating' => round((float) ($product->average_rating ?? 0), 1),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'rating_breakdown' => $ratingBreakdown,
            'items' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function normalizeShopFilters(array $filters): array
    {
        $categoryId = $filters['category_id'] ?? null;

        return [
            'search' => filled($filters['search'] ?? null) ? (string) $filters['search'] : null,
            'category_id' => filled($categoryId) ? (int) $categoryId : null,
            'price_min' => isset($filters['price_min']) && $filters['price_min'] !== ''
                ? (float) $filters['price_min']
                : null,
            'price_max' => isset($filters['price_max']) && $filters['price_max'] !== ''
                ? (float) $filters['price_max']
                : null,
            'min_rating' => isset($filters['min_rating']) && $filters['min_rating'] !== ''
                ? (float) $filters['min_rating']
                : null,
            'in_stock' => array_key_exists('in_stock', $filters)
                ? filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
            'out_of_stock' => array_key_exists('out_of_stock', $filters)
                ? filter_var($filters['out_of_stock'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
            'sort' => filled($filters['sort'] ?? null) ? (string) $filters['sort'] : 'recommended',
            'per_page' => (int) ($filters['per_page'] ?? 9),
        ];
    }

    /**
     * Nested active categories for the shop filter sidebar.
     *
     * @return list<array{id: int|null, name: string, products_count: int, image: string|null, children: list<array<string, mixed>>}>
     */
    private function shopCategoryOptions(): array
    {
        $publishedConstraint = fn ($query) => $query->published();

        $total = Product::query()->published()->count();

        $categories = Category::query()
            ->status('active')
            ->ordered()
            ->withCount(['products' => $publishedConstraint])
            ->get(['id', 'name', 'parent_id', 'image', 'sort_order']);

        $tree = $this->buildShopCategoryTree($categories);

        return array_merge([
            [
                'id' => null,
                'name' => 'All',
                'products_count' => $total,
                'image' => null,
                'children' => [],
            ],
        ], $tree);
    }

    /**
     * @param  Collection<int, Category>|EloquentCollection<int, Category>  $categories
     * @return list<array{id: int, name: string, products_count: int, image: string|null, children: list<array<string, mixed>>}>
     */
    private function buildShopCategoryTree(Collection|EloquentCollection $categories, ?int $parentId = null): array
    {
        return $categories
            ->filter(fn (Category $category) => $category->parent_id === $parentId)
            ->values()
            ->map(function (Category $category) use ($categories) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products_count' => (int) $category->products_count,
                    'image' => Category::toPublicUrl($category->image),
                    'children' => $this->buildShopCategoryTree($categories, $category->id),
                ];
            })
            ->all();
    }

    /**
     * @return array{min: float, max: float}
     */
    private function shopPriceBounds(): array
    {
        $row = Product::query()
            ->published()
            ->selectRaw('MIN(COALESCE(sale_price, regular_price)) as min_price')
            ->selectRaw('MAX(COALESCE(sale_price, regular_price)) as max_price')
            ->first();

        $min = (float) ($row?->min_price ?? 0);
        $max = (float) ($row?->max_price ?? 0);

        if ($max < $min) {
            $max = $min;
        }

        return [
            'min' => round($min, 2),
            'max' => round(max($max, $min), 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>|null  $galleryImages
     */
    public function create(
        array $data,
        ?UploadedFile $thumbnail = null,
        ?UploadedFile $ogImage = null,
        ?array $galleryImages = null,
    ): Product {
        return DB::transaction(function () use ($data, $thumbnail, $ogImage, $galleryImages) {
            $payload = $this->productPayload($data);

            if ($thumbnail) {
                $payload['thumbnail'] = $thumbnail->store('products/thumbnails', 'public');
            }

            if ($ogImage) {
                $payload['og_image'] = $ogImage->store('products/og', 'public');
            }

            if (! array_key_exists('stock_status', $data) || blank($data['stock_status'])) {
                $payload['stock_status'] = $this->deriveStockStatus(
                    (int) ($payload['stock_quantity'] ?? 0),
                    (bool) ($payload['allow_backorders'] ?? false),
                );
            }

            $product = Product::query()->create($payload);

            $product->categories()->sync($data['category_ids'] ?? []);
            $product->attributeValues()->sync($data['attribute_value_ids'] ?? []);
            $this->storeGalleryImages($product, $galleryImages ?? []);

            return $product->load(['categories', 'attributeValues', 'images']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>|null  $galleryImages
     * @param  list<int>|null  $removeImageIds
     */
    public function update(
        Product $product,
        array $data,
        ?UploadedFile $thumbnail = null,
        ?UploadedFile $ogImage = null,
        ?array $galleryImages = null,
        ?array $removeImageIds = null,
    ): Product {
        return DB::transaction(function () use ($product, $data, $thumbnail, $ogImage, $galleryImages, $removeImageIds) {
            $payload = $this->productPayload($data);

            if ($thumbnail) {
                $this->deleteStoredImage($product->thumbnail);
                $payload['thumbnail'] = $thumbnail->store('products/thumbnails', 'public');
            }

            if ($ogImage) {
                $this->deleteStoredImage($product->og_image);
                $payload['og_image'] = $ogImage->store('products/og', 'public');
            }

            if (! array_key_exists('stock_status', $data) || blank($data['stock_status'])) {
                $payload['stock_status'] = $this->deriveStockStatus(
                    (int) ($payload['stock_quantity'] ?? $product->stock_quantity),
                    (bool) ($payload['allow_backorders'] ?? $product->allow_backorders),
                );
            }

            $product->update($payload);

            if (array_key_exists('category_ids', $data)) {
                $product->categories()->sync($data['category_ids'] ?? []);
            }

            if (array_key_exists('attribute_value_ids', $data)) {
                $product->attributeValues()->sync($data['attribute_value_ids'] ?? []);
            }

            $this->removeGalleryImages($product, $removeImageIds ?? []);
            $this->storeGalleryImages($product, $galleryImages ?? []);

            return $product->fresh(['categories', 'attributeValues', 'images']);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->load('images');

            foreach ($product->images as $image) {
                $image->deleteFile();
                $image->delete();
            }

            $this->deleteStoredImage($product->thumbnail);
            $this->deleteStoredImage($product->og_image);
            $product->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productPayload(array $data): array
    {
        return collect($data)
            ->except([
                'category_ids',
                'attribute_value_ids',
                'thumbnail',
                'og_image',
                'images',
                'remove_image_ids',
            ])
            ->all();
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function storeGalleryImages(Product $product, array $files): void
    {
        if ($files === []) {
            return;
        }

        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $sortOrder++;

            $product->images()->create([
                'path' => $file->store('products/gallery', 'public'),
                'sort_order' => $sortOrder,
            ]);
        }
    }

    /**
     * @param  list<int>  $imageIds
     */
    private function removeGalleryImages(Product $product, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $product->images()
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            /** @var ProductImage $image */
            $image->deleteFile();
            $image->delete();
        }
    }

    private function deriveStockStatus(int $quantity, bool $allowBackorders): string
    {
        if ($quantity > 0) {
            return 'in_stock';
        }

        return $allowBackorders ? 'on_backorder' : 'out_of_stock';
    }

    private function deleteStoredImage(?string $path): void
    {
        if (
            $path
            && ! str_starts_with($path, 'http://')
            && ! str_starts_with($path, 'https://')
            && ! str_starts_with($path, '/')
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}
