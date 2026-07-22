<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Fixed moderation profiles for admin UI / stats testing.
     *
     * @var list<array{
     *     title: string,
     *     status: string,
     *     rating: int,
     *     flag_reason?: string|null,
     *     admin_response?: string|null
     * }>
     */
    private const MODERATION_PROFILES = [
        [
            'title' => 'Pending — waiting for approval',
            'status' => 'pending',
            'rating' => 5,
        ],
        [
            'title' => 'Pending — mixed feedback',
            'status' => 'pending',
            'rating' => 3,
        ],
        [
            'title' => 'Pending — short review',
            'status' => 'pending',
            'rating' => 4,
        ],
        [
            'title' => 'Approved — absolutely delicious!',
            'status' => 'approved',
            'rating' => 5,
            'admin_response' => 'Thank you for the kind words — glad you enjoyed it!',
        ],
        [
            'title' => 'Approved — best bakery in town',
            'status' => 'approved',
            'rating' => 5,
        ],
        [
            'title' => 'Approved — will order again',
            'status' => 'approved',
            'rating' => 4,
        ],
        [
            'title' => 'Approved — fresh and perfect',
            'status' => 'approved',
            'rating' => 5,
        ],
        [
            'title' => 'Flagged — possible spam',
            'status' => 'flagged',
            'rating' => 1,
            'flag_reason' => 'Possible spam content',
        ],
        [
            'title' => 'Flagged — offensive language',
            'status' => 'flagged',
            'rating' => 2,
            'flag_reason' => 'Offensive language',
        ],
        [
            'title' => 'Rejected — fake review',
            'status' => 'rejected',
            'rating' => 1,
        ],
        [
            'title' => 'Rejected — irrelevant content',
            'status' => 'rejected',
            'rating' => 2,
        ],
    ];

    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->get();
        $products = Product::query()->where('status', 'active')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('ReviewSeeder skipped: need active products and customer users.');

            return;
        }

        foreach (self::MODERATION_PROFILES as $index => $profile) {
            $customer = $customers[$index % $customers->count()];
            $product = $products[$index % $products->count()];

            if (Review::query()->where('title', $profile['title'])->exists()) {
                continue;
            }

            $order = Order::query()
                ->where('user_id', $customer->id)
                ->where('status', 'delivered')
                ->inRandomOrder()
                ->first();

            $isFlagged = $profile['status'] === 'flagged';

            Review::query()->create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'order_id' => $order?->id,
                'rating' => $profile['rating'],
                'title' => $profile['title'],
                'body' => fake()->paragraph(2),
                'status' => $profile['status'],
                'flag_reason' => $profile['flag_reason'] ?? null,
                'flagged_at' => $isFlagged ? now()->subDays(fake()->numberBetween(1, 10)) : null,
                'admin_response' => $profile['admin_response'] ?? null,
                'admin_response_at' => isset($profile['admin_response'])
                    ? now()->subDays(fake()->numberBetween(1, 5))
                    : null,
                'is_verified_purchase' => $order !== null,
                'helpful_yes' => fake()->numberBetween(0, 20),
                'helpful_no' => fake()->numberBetween(0, 3),
            ]);
        }

        // Extra variety via factory states (skip if we already have a healthy pool).
        if (Review::query()->count() >= 20) {
            return;
        }

        Review::factory(3)
            ->pending()
            ->recycle($customers)
            ->recycle($products)
            ->create();

        Review::factory(4)
            ->approved()
            ->recycle($customers)
            ->recycle($products)
            ->create();

        Review::factory(2)
            ->flagged()
            ->recycle($customers)
            ->recycle($products)
            ->create();

        Review::factory(2)
            ->rejected()
            ->recycle($customers)
            ->recycle($products)
            ->create();

        Review::factory(1)
            ->approved()
            ->withAdminResponse()
            ->recycle($customers)
            ->recycle($products)
            ->create();
    }
}
