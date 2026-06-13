<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Classic Butter Croissant',
                'slug' => 'classic-butter-croissant',
                'category' => 'croissants-pastries',
                'short_description' => 'Golden, flaky layers with French butter.',
                'regular_price' => 4.50,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-10001',
                'og_image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
            ],
            [
                'name' => 'Almond Croissant',
                'slug' => 'almond-croissant',
                'category' => 'croissants-pastries',
                'short_description' => 'Filled with almond cream and topped with sliced almonds.',
                'regular_price' => 5.25,
                'sale_price' => 4.75,
                'is_featured' => true,
                'sku' => 'CB-10002',
                'og_image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80',
            ],
            [
                'name' => 'Pain au Chocolat',
                'slug' => 'pain-au-chocolat',
                'category' => 'croissants-pastries',
                'short_description' => 'Dark chocolate batons wrapped in laminated dough.',
                'regular_price' => 4.95,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-10003',
                'og_image' => 'https://images.unsplash.com/photo-1607478900766-efe13248b125?w=800&q=80',
            ],
            [
                'name' => 'Raspberry Danish',
                'slug' => 'raspberry-danish',
                'category' => 'croissants-pastries',
                'short_description' => 'Cream cheese Danish with house raspberry compote.',
                'regular_price' => 5.50,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-10004',
                'og_image' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800&q=80',
            ],
            [
                'name' => 'Chocolate Celebration Cake',
                'slug' => 'chocolate-celebration-cake',
                'category' => 'cakes-celebrations',
                'short_description' => 'Rich dark chocolate layers with ganache frosting.',
                'regular_price' => 42.00,
                'sale_price' => 36.00,
                'is_featured' => true,
                'sku' => 'CB-20001',
                'og_image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
            ],
            [
                'name' => 'Vanilla Bean Birthday Cake',
                'slug' => 'vanilla-bean-birthday-cake',
                'category' => 'cakes-celebrations',
                'short_description' => 'Madagascar vanilla sponge with Swiss meringue buttercream.',
                'regular_price' => 38.00,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-20002',
                'og_image' => 'https://images.unsplash.com/photo-1464349095431-e9a21285b5fa?w=800&q=80',
            ],
            [
                'name' => 'Red Velvet Cupcake Box',
                'slug' => 'red-velvet-cupcake-box',
                'category' => 'cakes-celebrations',
                'short_description' => 'Six red velvet cupcakes with cream cheese frosting.',
                'regular_price' => 24.00,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-20003',
                'og_image' => 'https://images.unsplash.com/photo-1614707267537-b85aaf00feff?w=800&q=80',
            ],
            [
                'name' => 'Lemon Drizzle Loaf Cake',
                'slug' => 'lemon-drizzle-loaf-cake',
                'category' => 'cakes-celebrations',
                'short_description' => 'Moist lemon sponge with citrus glaze.',
                'regular_price' => 18.50,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-20004',
                'og_image' => 'https://images.unsplash.com/photo-1519915028121-7d68481794e9?w=800&q=80',
            ],
            [
                'name' => 'Country Sourdough Loaf',
                'slug' => 'country-sourdough-loaf',
                'category' => 'artisan-breads',
                'short_description' => '48-hour fermented sourdough with a crisp crust.',
                'regular_price' => 7.50,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-30001',
                'og_image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
            ],
            [
                'name' => 'Seeded Multigrain Bread',
                'slug' => 'seeded-multigrain-bread',
                'category' => 'artisan-breads',
                'short_description' => 'Hearty loaf packed with sunflower, flax, and sesame seeds.',
                'regular_price' => 6.75,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-30002',
                'og_image' => 'https://images.unsplash.com/photo-1549931319-a545dcf473de?w=800&q=80',
            ],
            [
                'name' => 'Ciabatta Rolls (4 pack)',
                'slug' => 'ciabatta-rolls-4-pack',
                'category' => 'artisan-breads',
                'short_description' => 'Light, airy Italian rolls perfect for sandwiches.',
                'regular_price' => 5.25,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-30003',
                'og_image' => 'https://images.unsplash.com/photo-1612182062936-2580b7757252?w=800&q=80',
            ],
            [
                'name' => 'Double Chocolate Chip Cookie',
                'slug' => 'double-chocolate-chip-cookie',
                'category' => 'cookies-treats',
                'short_description' => 'Soft-baked cookie with dark and milk chocolate chunks.',
                'regular_price' => 3.25,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-40001',
                'og_image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80',
            ],
            [
                'name' => 'French Macaron Assortment',
                'slug' => 'french-macaron-assortment',
                'category' => 'cookies-treats',
                'short_description' => 'Box of six macarons in rotating seasonal flavours.',
                'regular_price' => 16.00,
                'sale_price' => 14.00,
                'is_featured' => true,
                'sku' => 'CB-40002',
                'og_image' => 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=800&q=80',
            ],
            [
                'name' => 'Shortbread Gift Tin',
                'slug' => 'shortbread-gift-tin',
                'category' => 'cookies-treats',
                'short_description' => 'Buttery Scottish shortbread — an ideal gift.',
                'regular_price' => 22.00,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-40003',
                'og_image' => 'https://images.unsplash.com/photo-1558961363-fa8aad7cdb11?w=800&q=80',
            ],
            [
                'name' => 'Pumpkin Spice Muffin',
                'slug' => 'pumpkin-spice-muffin',
                'category' => 'seasonal-specials',
                'short_description' => 'Warm spices and pumpkin purée topped with streusel.',
                'regular_price' => 4.25,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-50001',
                'og_image' => 'https://images.unsplash.com/photo-1607958996338-0106d5c4a1c9?w=800&q=80',
            ],
            [
                'name' => 'Hot Cross Buns (6 pack)',
                'slug' => 'hot-cross-buns-6-pack',
                'category' => 'seasonal-specials',
                'short_description' => 'Spiced fruit buns with a sweet cross glaze.',
                'regular_price' => 9.50,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-50002',
                'og_image' => 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=800&q=80',
            ],
            [
                'name' => 'Strawberry Tart',
                'slug' => 'strawberry-tart',
                'category' => 'seasonal-specials',
                'short_description' => 'Vanilla pastry cream and fresh strawberries on shortcrust.',
                'regular_price' => 6.50,
                'sale_price' => null,
                'is_featured' => true,
                'sku' => 'CB-50003',
                'og_image' => 'https://images.unsplash.com/photo-1565958011703-44f9824cba31?w=800&q=80',
            ],
            [
                'name' => 'Espresso Éclair',
                'slug' => 'espresso-eclair',
                'category' => 'croissants-pastries',
                'short_description' => 'Choux pastry filled with espresso crème pâtissière.',
                'regular_price' => 5.75,
                'sale_price' => null,
                'is_featured' => false,
                'sku' => 'CB-10005',
                'og_image' => 'https://images.unsplash.com/photo-1621303837174-897243bd4083?w=800&q=80',
                'stock_status' => 'out_of_stock',
                'stock_quantity' => 0,
            ],
        ];

        $attributeValues = AttributeValue::query()->pluck('id');

        foreach ($products as $data) {
            $category = Category::query()->where('slug', $data['category'])->firstOrFail();

            $product = Product::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'],
                'description' => $data['short_description'].' '.fake()->paragraph(2),
                'regular_price' => $data['regular_price'],
                'sale_price' => $data['sale_price'],
                'cost_price' => round($data['regular_price'] * 0.4, 2),
                'sku' => $data['sku'],
                'stock_quantity' => $data['stock_quantity'] ?? fake()->numberBetween(15, 80),
                'low_stock_threshold' => 5,
                'allow_backorders' => false,
                'stock_status' => $data['stock_status'] ?? 'in_stock',
                'is_featured' => $data['is_featured'],
                'is_active' => true,
                'status' => 'active',
                'meta_description' => $data['short_description'],
                'meta_keywords' => ['bakery', 'fresh', 'artisan'],
                'og_image' => $data['og_image'],
                'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            ]);

            $category->products()->attach($product->id);

            if ($attributeValues->isNotEmpty()) {
                $product->attributeValues()->attach(
                    $attributeValues->random(fake()->numberBetween(1, 2))->all()
                );
            }
        }

        Product::factory(6)->create()->each(function (Product $product) use ($attributeValues) {
            $categories = Category::query()->inRandomOrder()->limit(fake()->numberBetween(1, 2))->pluck('id');
            $product->categories()->attach($categories);

            if ($attributeValues->isNotEmpty()) {
                $product->attributeValues()->attach(
                    $attributeValues->random(fake()->numberBetween(1, 3))->all()
                );
            }
        });
    }
}
