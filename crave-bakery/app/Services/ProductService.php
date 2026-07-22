<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
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
