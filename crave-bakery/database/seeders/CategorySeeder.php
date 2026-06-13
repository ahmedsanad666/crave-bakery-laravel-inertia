<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Croissants & Pastries',
                'slug' => 'croissants-pastries',
                'description' => 'Buttery, flaky classics baked fresh every morning.',
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Cakes & Celebrations',
                'slug' => 'cakes-celebrations',
                'description' => 'Show-stopping cakes for birthdays, weddings, and every occasion.',
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Artisan Breads',
                'slug' => 'artisan-breads',
                'description' => 'Slow-fermented sourdoughs and rustic loaves with crisp crusts.',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Cookies & Treats',
                'slug' => 'cookies-treats',
                'description' => 'Hand-decorated cookies, macarons, and bite-sized indulgences.',
                'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Seasonal Specials',
                'slug' => 'seasonal-specials',
                'description' => 'Limited-edition flavours inspired by the season.',
                'image' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800&q=80',
                'show_in_homepage' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->create([
                ...$category,
                'default_sort' => 'newest',
                'show_in_navigation' => true,
                'status' => 'active',
                'meta_description' => $category['description'],
                'meta_keywords' => ['bakery', 'fresh', 'artisan'],
            ]);
        }
    }
}
