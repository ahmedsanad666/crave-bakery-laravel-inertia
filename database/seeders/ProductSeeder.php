<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{images: list<string>, products: list<array<string, mixed>>} $data */
        $data = require __DIR__.'/data/coffee_products.php';

        $images = $data['images'];
        $categories = Category::query()->get()->keyBy('slug');

        $roastValues = $this->valuesForAttribute('Roast Level');
        $grindValues = $this->valuesForAttribute('Grind');
        $bagValues = $this->valuesForAttribute('Bag Size');

        DB::transaction(function () use ($data, $images, $categories, $roastValues, $grindValues, $bagValues) {
            foreach ($data['products'] as $row) {
                $thumbnail = $images[$row['image_indexes'][0]] ?? $images[0];
                $gallerySecondary = $images[$row['image_indexes'][1] ?? 0] ?? $thumbnail;

                $product = Product::query()->updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'name' => $row['name'],
                        'short_description' => $row['short_description'],
                        'description' => $row['description'],
                        'regular_price' => $row['regular_price'],
                        'sale_price' => $row['sale_price'],
                        'cost_price' => round($row['regular_price'] * 0.45, 2),
                        'sku' => $row['sku'],
                        'barcode' => null,
                        'stock_quantity' => $row['stock_quantity'],
                        'low_stock_threshold' => 8,
                        'allow_backorders' => false,
                        'stock_status' => $row['stock_status'],
                        'is_featured' => $row['is_featured'],
                        'is_active' => true,
                        'thumbnail' => $thumbnail,
                        'status' => 'active',
                        'meta_title' => $row['name'].' | Coffee Ship',
                        'meta_description' => $row['short_description'],
                        'meta_keywords' => ['coffee', 'specialty', 'coffee ship'],
                        'og_image' => $thumbnail,
                        'canonical_url' => null,
                        'published_at' => now()->subDays(random_int(1, 60)),
                    ],
                );

                $categoryIds = collect($row['category_slugs'])
                    ->map(fn (string $slug) => $categories->get($slug)?->id)
                    ->filter()
                    ->values()
                    ->all();

                if ($categoryIds !== []) {
                    $product->categories()->sync($categoryIds);
                }

                $attributeValueIds = collect([
                    $roastValues->get($row['roast'])?->id,
                    $grindValues->get($row['grind'])?->id,
                    $bagValues->get($row['bag_size'])?->id,
                ])->filter()->values()->all();

                if ($attributeValueIds !== []) {
                    $product->attributeValues()->sync($attributeValueIds);
                }

                ProductImage::query()->where('product_id', $product->id)->delete();

                foreach ([$thumbnail, $gallerySecondary] as $sort => $path) {
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'sort_order' => $sort,
                    ]);
                }
            }
        });
    }

    /**
     * @return \Illuminate\Support\Collection<string, AttributeValue>
     */
    private function valuesForAttribute(string $name)
    {
        $attribute = Attribute::query()->where('name', $name)->first();

        if (! $attribute) {
            return collect();
        }

        return AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->get()
            ->keyBy('value');
    }
}
