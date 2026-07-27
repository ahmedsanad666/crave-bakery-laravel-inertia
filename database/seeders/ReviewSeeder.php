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
        $customers = User::query()->where('role', 'user')->orderBy('id')->get();
        $deliveredOrders = Order::query()
            ->with('orderItems')
            ->where('status', 'delivered')
            ->get();

        $reviewCopy = [
            ['Great everyday brew', 'Smooth chocolate notes and very consistent across bags. Will reorder.'],
            ['Bright and floral', 'Exactly as described — jasmine on the nose and clean finish in pour-over.'],
            ['Perfect for espresso', 'Sweet crema and no bitterness. Works beautifully with oat milk.'],
            ['Fresh roast arrived fast', 'Packaging was solid and roast date was recent. Cupped fantastic.'],
            ['Cold brew favourite', 'Low acid and naturally sweet after a 16-hour steep. Highly recommend.'],
            ['Gift box hit the mark', 'Presentation was lovely and the tasting card helped my friend brew it right.'],
            ['AeroPress star', 'Clarity with nice body — my weekday morning recipe now.'],
            ['Worth the premium', 'Geisha-level florals without being thin. Special-occasion coffee.'],
        ];

        $created = 0;

        foreach ($deliveredOrders as $order) {
            foreach ($order->orderItems as $item) {
                if ($created >= 40) {
                    break 2;
                }

                if (! $item->product_id) {
                    continue;
                }

                $user = $customers->firstWhere('id', $order->user_id) ?? $customers->random();
                $copy = $reviewCopy[$created % count($reviewCopy)];

                Review::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $item->product_id,
                        'order_id' => $order->id,
                    ],
                    [
                        'rating' => random_int(4, 5),
                        'title' => $copy[0],
                        'body' => $copy[1],
                        'status' => 'approved',
                        'is_verified_purchase' => true,
                        'helpful_yes' => random_int(0, 18),
                        'helpful_no' => random_int(0, 2),
                        'admin_response' => $created % 5 === 0
                            ? 'Thanks for sharing your brew notes — glad it showed up in the cup!'
                            : null,
                        'admin_response_at' => $created % 5 === 0 ? now()->subDays(2) : null,
                    ],
                );

                $created++;
            }
        }

        // Fill remaining reviews on featured products if needed.
        if ($created < 40) {
            $products = Product::query()->where('is_featured', true)->get();

            while ($created < 40 && $products->isNotEmpty() && $customers->isNotEmpty()) {
                $product = $products[$created % $products->count()];
                $user = $customers[$created % $customers->count()];
                $copy = $reviewCopy[$created % count($reviewCopy)];

                Review::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'order_id' => null,
                    ],
                    [
                        'rating' => random_int(3, 5),
                        'title' => $copy[0],
                        'body' => $copy[1],
                        'status' => 'approved',
                        'is_verified_purchase' => false,
                        'helpful_yes' => random_int(0, 10),
                        'helpful_no' => random_int(0, 3),
                    ],
                );

                $created++;
            }
        }
    }
}
