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
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(12),
            'parent_id' => null,
            'image' => fake()->randomElement($this->categoryImages()),
            'banner_image' => null,
            'image_alt' => fake()->sentence(3),
            'default_sort' => 'newest',
            'show_in_navigation' => true,
            'show_in_homepage' => fake()->boolean(40),
            'meta_title' => null,
            'meta_description' => fake()->sentence(10),
            'meta_keywords' => ['bakery', 'pastry', 'fresh'],
            'og_image' => null,
            'canonical_url' => null,
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => 'active',
        ];
    }

    public function homepage(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_homepage' => true,
            'show_in_navigation' => true,
            'status' => 'active',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'show_in_homepage' => false,
        ]);
    }

    /**
     * @return list<string>
     */
    private function categoryImages(): array
    {
        return [
            'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
            'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
            'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
            'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=800&q=80',
        ];
    }
}
