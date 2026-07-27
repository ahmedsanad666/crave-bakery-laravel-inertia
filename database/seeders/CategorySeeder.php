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
                'name' => 'AeroPress',
                'slug' => 'aeropress',
                'description' => 'Beans and kits tuned for AeroPress clarity, body, and travel-friendly brewing.',
                'image' => 'https://images.unsplash.com/photo-1510590337019-5ef8d3d32116?w=800&q=80',
                'sort_order' => 1,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Blends',
                'slug' => 'blends',
                'description' => 'House and seasonal blends built for balance — espresso, drip, and everyday cups.',
                'image' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80',
                'sort_order' => 2,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Coffee Beans & Grounds',
                'slug' => 'coffee-beans-grounds',
                'description' => 'Freshly roasted beans and ready-to-brew grounds in whole bean or grind options.',
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
                'sort_order' => 3,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Pacific Coffees',
                'slug' => 'pacific-coffees',
                'description' => 'Bright, elegant lots from Pacific and Central American growing regions.',
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                'sort_order' => 4,
                'show_in_footer' => false,
            ],
            [
                'name' => 'Espresso',
                'slug' => 'espresso',
                'description' => 'Espresso-focused roasts and gear for rich crema and milk drinks.',
                'image' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=800&q=80',
                'sort_order' => 5,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Single Origin',
                'slug' => 'single-origin',
                'description' => 'Traceable farm and regional lots with distinct tasting notes.',
                'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&q=80',
                'sort_order' => 6,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Cold Brew',
                'slug' => 'cold-brew',
                'description' => 'Coarse grinds, concentrates, and kits for smooth iced coffee.',
                'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80',
                'sort_order' => 7,
                'show_in_footer' => false,
            ],
            [
                'name' => 'Equipment',
                'slug' => 'equipment',
                'description' => 'Grinders, drippers, kettles, and tools for better brewing at home.',
                'image' => 'https://images.unsplash.com/photo-1498804103079-a6351b050096?w=800&q=80',
                'sort_order' => 8,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Gift Sets',
                'slug' => 'gift-sets',
                'description' => 'Ready-to-gift boxes pairing coffee, gear, and tasting guides.',
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
                'sort_order' => 9,
                'show_in_footer' => true,
            ],
            [
                'name' => 'Instant & Concentrates',
                'slug' => 'instant-concentrates',
                'description' => 'Specialty instant and cold brew concentrates for fast, quality cups.',
                'image' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=800&q=80',
                'sort_order' => 10,
                'show_in_footer' => false,
            ],
        ];

        foreach ($categories as $data) {
            Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'parent_id' => null,
                    'image' => $data['image'],
                    'banner_image' => $data['image'],
                    'image_alt' => $data['name'].' coffee category',
                    'default_sort' => 'newest',
                    'show_in_navigation' => true,
                    'show_in_footer' => $data['show_in_footer'],
                    'meta_title' => $data['name'].' | Coffee Ship',
                    'meta_description' => $data['description'],
                    'meta_keywords' => ['coffee', strtolower($data['name']), 'specialty'],
                    'og_image' => $data['image'],
                    'canonical_url' => null,
                    'sort_order' => $data['sort_order'],
                    'status' => 'active',
                ],
            );
        }
    }
}
