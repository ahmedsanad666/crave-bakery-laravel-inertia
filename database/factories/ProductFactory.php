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
        $origin = fake()->randomElement([
            'Ethiopia', 'Colombia', 'Brazil', 'Guatemala', 'Kenya',
            'Costa Rica', 'Sumatra', 'Peru', 'Rwanda', 'Honduras',
        ]);
        $style = fake()->randomElement([
            'Yirgacheffe', 'Single Origin', 'Estate Reserve', 'Dark Roast',
            'Honey Process', 'Washed', 'Natural', 'Espresso Blend',
            'Cold Brew', 'Filter Roast',
        ]);
        $name = fake()->unique()->numerify($origin.' '.$style.' ###');
        $image = fake()->randomElement($this->productImages());
        $regularPrice = fake()->randomFloat(2, 12, 85);
        $onSale = fake()->boolean(25);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => "Specialty {$origin} coffee with notes of ".fake()->randomElement([
                'citrus and jasmine', 'dark chocolate and caramel', 'berry and cocoa',
                'honey and stone fruit', 'nutty caramel sweetness', 'bright lemon and tea',
            ]).'.',
            'description' => "Sourced from smallholder farms in {$origin}, this lot is roasted in small batches for peak freshness. Ideal for pour-over, espresso, or cold brew depending on grind. Vacuum-sealed for aroma retention.",
            'regular_price' => $regularPrice,
            'sale_price' => $onSale ? round($regularPrice * fake()->randomFloat(2, 0.7, 0.9), 2) : null,
            'cost_price' => round($regularPrice * 0.45, 2),
            'sku' => 'CS-'.fake()->unique()->numerify('#####'),
            'barcode' => fake()->optional()->ean13(),
            'stock_quantity' => fake()->numberBetween(0, 120),
            'low_stock_threshold' => 8,
            'allow_backorders' => false,
            'stock_status' => 'in_stock',
            'is_featured' => fake()->boolean(25),
            'is_active' => true,
            'thumbnail' => $image,
            'status' => 'active',
            'meta_title' => null,
            'meta_description' => "Buy {$name} — freshly roasted specialty coffee shipped from Coffee Ship.",
            'meta_keywords' => ['coffee', 'beans', 'specialty', Str::lower($origin)],
            'og_image' => $image,
            'canonical_url' => null,
            'published_at' => now()->subDays(fake()->numberBetween(1, 90)),
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
            $regular = $attributes['regular_price'] ?? fake()->randomFloat(2, 15, 70);

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
            'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80',
            'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
            'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80',
            'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&q=80',
            'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=800&q=80',
            'https://images.unsplash.com/photo-1498804103079-a6351b050096?w=800&q=80',
            'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80',
            'https://images.unsplash.com/photo-1510590337019-5ef8d3d32116?w=800&q=80',
            'https://images.unsplash.com/photo-1610889556528-9a770639fc2f?w=800&q=80',
            'https://images.unsplash.com/photo-1611854779393-1b2da9d400fe?w=800&q=80',
            'https://images.unsplash.com/photo-1459755486867-b55449bb39ff?w=800&q=80',
        ];
    }
}
