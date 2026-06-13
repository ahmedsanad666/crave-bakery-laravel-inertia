<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->get();
        $products = Product::query()->where('status', 'active')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $reviewTitles = [
            'Absolutely delicious!',
            'Best bakery in town',
            'Will order again',
            'Fresh and perfect',
            'A new favourite',
            'Exceeded expectations',
            'Perfect for celebrations',
        ];

        foreach ($products->random(min(12, $products->count())) as $product) {
            $reviewCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $reviewCount; $i++) {
                $customer = $customers->random();
                $order = Order::query()
                    ->where('user_id', $customer->id)
                    ->where('status', 'delivered')
                    ->inRandomOrder()
                    ->first();

                Review::query()->create([
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                    'order_id' => $order?->id,
                    'rating' => fake()->numberBetween(4, 5),
                    'title' => fake()->randomElement($reviewTitles),
                    'body' => fake()->paragraph(2),
                    'status' => 'approved',
                    'is_verified_purchase' => $order !== null,
                    'helpful_yes' => fake()->numberBetween(0, 20),
                    'helpful_no' => fake()->numberBetween(0, 3),
                ]);
            }
        }

        Review::factory(8)
            ->approved()
            ->recycle($customers)
            ->recycle($products)
            ->create();
    }
}
