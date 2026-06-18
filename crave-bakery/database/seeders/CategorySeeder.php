<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Croissants & Pastries',
                'slug' => 'croissants-pastries',
                'description' => 'Buttery, flaky classics baked fresh every morning.',
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'Butter Croissants',
                        'slug' => 'butter-croissants',
                        'description' => 'Classic laminated croissants with French butter.',
                        'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80',
                    ],
                    [
                        'name' => 'Almond Pastries',
                        'slug' => 'almond-pastries',
                        'description' => 'Frangipane-filled pastries topped with sliced almonds.',
                        'image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800&q=80',
                    ],
                    [
                        'name' => 'Danishes',
                        'slug' => 'danishes',
                        'description' => 'Fruit and cream cheese Danish pastries.',
                        'image' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800&q=80',
                    ],
                    [
                        'name' => 'Savory Pastries',
                        'slug' => 'savory-pastries',
                        'description' => 'Ham, cheese, and herb-filled baked goods.',
                        'image' => 'https://images.unsplash.com/photo-1607478900766-efe13248b125?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Cakes & Celebrations',
                'slug' => 'cakes-celebrations',
                'description' => 'Show-stopping cakes for birthdays, weddings, and every occasion.',
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Birthday Cakes',
                        'slug' => 'birthday-cakes',
                        'description' => 'Custom celebration cakes for all ages.',
                        'image' => 'https://images.unsplash.com/photo-1464349095431-e9a21285b5fa?w=800&q=80',
                    ],
                    [
                        'name' => 'Wedding Cakes',
                        'slug' => 'wedding-cakes',
                        'description' => 'Elegant tiered cakes for your special day.',
                        'image' => 'https://images.unsplash.com/photo-1535254973040-607b474cb50d?w=800&q=80',
                    ],
                    [
                        'name' => 'Cupcakes',
                        'slug' => 'cupcakes',
                        'description' => 'Individual treats perfect for parties and gifts.',
                        'image' => 'https://images.unsplash.com/photo-1614707267537-b85aaff9e7c9?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Artisan Breads',
                'slug' => 'artisan-breads',
                'description' => 'Slow-fermented sourdoughs and rustic loaves with crisp crusts.',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Sourdough',
                        'slug' => 'sourdough',
                        'description' => 'Naturally leavened loaves with deep flavor.',
                        'image' => 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=800&q=80',
                    ],
                    [
                        'name' => 'Baguettes & Rolls',
                        'slug' => 'baguettes-rolls',
                        'description' => 'Crusty baguettes and dinner rolls baked daily.',
                        'image' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=800&q=80',
                    ],
                    [
                        'name' => 'Specialty Loaves',
                        'slug' => 'specialty-loaves',
                        'description' => 'Seeded, whole grain, and flavored artisan loaves.',
                        'image' => 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Cookies & Treats',
                'slug' => 'cookies-treats',
                'description' => 'Hand-decorated cookies, macarons, and bite-sized indulgences.',
                'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 4,
                'children' => [
                    [
                        'name' => 'Chocolate Chip Cookies',
                        'slug' => 'chocolate-chip-cookies',
                        'description' => 'Chewy cookies with premium chocolate chunks.',
                        'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80',
                    ],
                    [
                        'name' => 'Macarons',
                        'slug' => 'macarons',
                        'description' => 'Colorful French almond meringue sandwiches.',
                        'image' => 'https://images.unsplash.com/photo-1569860871744-9d5ad1c8c18e?w=800&q=80',
                    ],
                    [
                        'name' => 'Brownies & Bars',
                        'slug' => 'brownies-bars',
                        'description' => 'Fudgy brownies and dessert bars.',
                        'image' => 'https://images.unsplash.com/photo-1607920591416-5514d0c4b1c0?w=800&q=80',
                    ],
                    [
                        'name' => 'Decorated Cookies',
                        'slug' => 'decorated-cookies',
                        'description' => 'Hand-piped sugar cookies for holidays and events.',
                        'image' => 'https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Seasonal Specials',
                'slug' => 'seasonal-specials',
                'description' => 'Limited-edition flavours inspired by the season.',
                'image' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800&q=80',
                'show_in_homepage' => false,
                'sort_order' => 5,
                'children' => [
                    [
                        'name' => 'Spring Collection',
                        'slug' => 'spring-collection',
                        'description' => 'Light florals and berry-forward spring bakes.',
                        'image' => 'https://images.unsplash.com/photo-1464349095431-e9a21285b5fa?w=800&q=80',
                    ],
                    [
                        'name' => 'Holiday Favorites',
                        'slug' => 'holiday-favorites',
                        'description' => 'Festive treats for year-end celebrations.',
                        'image' => 'https://images.unsplash.com/photo-1576919228236-a296e30dd627?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Pies & Tarts',
                'slug' => 'pies-tarts',
                'description' => 'Buttery crusts filled with seasonal fruits and custards.',
                'image' => 'https://images.unsplash.com/photo-1535920527002-b35e967229eb?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 6,
                'children' => [
                    [
                        'name' => 'Fruit Pies',
                        'slug' => 'fruit-pies',
                        'description' => 'Apple, cherry, and mixed berry pies.',
                        'image' => 'https://images.unsplash.com/photo-1535920527002-b35e967229eb?w=800&q=80',
                    ],
                    [
                        'name' => 'Cream Pies',
                        'slug' => 'cream-pies',
                        'description' => 'Silky banana, chocolate, and coconut cream pies.',
                        'image' => 'https://images.unsplash.com/photo-1621303837174-89787a7d4729?w=800&q=80',
                    ],
                    [
                        'name' => 'Tartlets',
                        'slug' => 'tartlets',
                        'description' => 'Individual tarts with lemon curd and fresh fruit.',
                        'image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Coffee, tea, and chilled drinks to pair with your pastry.',
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                'show_in_homepage' => false,
                'sort_order' => 7,
                'children' => [
                    [
                        'name' => 'Coffee & Espresso',
                        'slug' => 'coffee-espresso',
                        'description' => 'Single-origin pour-overs, lattes, and espresso drinks.',
                        'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                    ],
                    [
                        'name' => 'Teas & Infusions',
                        'slug' => 'teas-infusions',
                        'description' => 'Loose-leaf teas and house-made herbal infusions.',
                        'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Breakfast & Brunch',
                'slug' => 'breakfast-brunch',
                'description' => 'Morning favorites to start the day at the bakery.',
                'image' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800&q=80',
                'show_in_homepage' => true,
                'sort_order' => 8,
                'children' => [
                    [
                        'name' => 'Muffins',
                        'slug' => 'muffins',
                        'description' => 'Blueberry, bran, and seasonal muffin varieties.',
                        'image' => 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=800&q=80',
                    ],
                    [
                        'name' => 'Scones',
                        'slug' => 'scones',
                        'description' => 'Tender scones with clotted cream and jam.',
                        'image' => 'https://images.unsplash.com/photo-1558961363-fa8eae64dcb2?w=800&q=80',
                    ],
                    [
                        'name' => 'Bagels',
                        'slug' => 'bagels',
                        'description' => 'Boiled and baked bagels with classic toppings.',
                        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                    ],
                    [
                        'name' => 'Quiches',
                        'slug' => 'quiches',
                        'description' => 'Savory egg tarts with cheese, herbs, and vegetables.',
                        'image' => 'https://images.unsplash.com/photo-1608039829572-78524f79c4c7?w=800&q=80',
                    ],
                ],
            ],
        ];

        foreach ($tree as $root) {
            $this->seedCategory($root);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedCategory(array $data, ?int $parentId = null): void
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        $category = Category::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'parent_id' => $parentId,
                'image' => $data['image'] ?? null,
                'default_sort' => 'newest',
                'show_in_navigation' => $data['show_in_navigation'] ?? true,
                'show_in_homepage' => $data['show_in_homepage'] ?? false,
                'sort_order' => $data['sort_order'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'meta_description' => $data['description'],
                'meta_keywords' => ['bakery', 'fresh', 'artisan'],
            ],
        );

        foreach ($children as $index => $child) {
            $child['sort_order'] = $child['sort_order'] ?? $index + 1;
            $this->seedCategory($child, $category->id);
        }
    }
}
