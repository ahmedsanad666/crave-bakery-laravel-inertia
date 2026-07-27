<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Single Origin Beans',
            'Espresso Blends',
            'Cold Brew Kits',
            'Pour-Over Gear',
            'Dark Roasts',
            'Light Roasts',
            'Decaf Selection',
            'Travel Mugs',
            'Gift Boxes',
            'Instant Specialty',
        ]).' '.fake()->unique()->numerify('##');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(14),
            'parent_id' => null,
            'image' => fake()->randomElement($this->categoryImages()),
            'banner_image' => null,
            'image_alt' => $name.' category',
            'default_sort' => 'newest',
            'show_in_navigation' => true,
            'show_in_footer' => fake()->boolean(40),
            'meta_title' => null,
            'meta_description' => fake()->sentence(12),
            'meta_keywords' => ['coffee', 'beans', 'specialty'],
            'og_image' => null,
            'canonical_url' => null,
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => 'active',
        ];
    }

    public function footer(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_footer' => true,
            'show_in_navigation' => true,
            'status' => 'active',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'show_in_footer' => false,
        ]);
    }

    /**
     * @return list<string>
     */
    private function categoryImages(): array
    {
        return [
            'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80',
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
            'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
            'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&q=80',
            'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80',
            'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=800&q=80',
        ];
    }
}
