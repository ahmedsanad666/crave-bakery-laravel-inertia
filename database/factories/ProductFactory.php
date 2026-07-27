<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $regularPrice = fake()->randomFloat(2, 3, 45);
        $onSale = fake()->boolean(25);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(8),
            'description' => fake()->paragraphs(3, true),
            'regular_price' => $regularPrice,
            'sale_price' => $onSale ? round($regularPrice * fake()->randomFloat(2, 0.65, 0.9), 2) : null,
            'cost_price' => round($regularPrice * 0.45, 2),
            'sku' => 'CB-'.fake()->unique()->numerify('#####'),
            'barcode' => fake()->optional()->ean13(),
            'stock_quantity' => fake()->numberBetween(0, 120),
            'low_stock_threshold' => 5,
            'allow_backorders' => false,
            'stock_status' => 'in_stock',
            'is_featured' => fake()->boolean(30),
            'is_active' => true,
            'status' => 'active',
            'meta_title' => null,
            'meta_description' => fake()->sentence(12),
            'meta_keywords' => ['bakery', 'fresh', 'artisan'],
            'og_image' => fake()->randomElement($this->productImages()),
            'canonical_url' => null,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => 'active',
            'is_active' => true,
            'stock_status' => 'in_stock',
            'stock_quantity' => fake()->numberBetween(10, 100),
        ]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $regular = $attributes['regular_price'] ?? fake()->randomFloat(2, 5, 40);

            return [
                'regular_price' => $regular,
                'sale_price' => round($regular * 0.8, 2),
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }

    /**
     * @return list<string>
     */
    private function productImages(): array
    {
        return [
            'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
            'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800&q=80',
            'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
            'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=800&q=80',
            'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=800&q=80',
            'https://images.unsplash.com/photo-1607478900766-efe13248b125?w=800&q=80',
        ];
    }
}
