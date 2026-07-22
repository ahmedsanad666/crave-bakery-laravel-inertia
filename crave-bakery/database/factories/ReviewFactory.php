<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(3),
            'status' => fake()->randomElement(['pending', 'approved', 'approved', 'approved']),
            'is_verified_purchase' => fake()->boolean(70),
            'helpful_yes' => fake()->numberBetween(0, 25),
            'helpful_no' => fake()->numberBetween(0, 5),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'flag_reason' => null,
            'flagged_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'flag_reason' => null,
            'flagged_at' => null,
        ]);
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'flagged',
            'flag_reason' => fake()->randomElement([
                'Possible spam content',
                'Offensive language',
                'Suspected fake review',
                'Irrelevant to product',
            ]),
            'flagged_at' => now()->subDays(fake()->numberBetween(1, 14)),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'flag_reason' => null,
            'flagged_at' => null,
        ]);
    }

    public function withAdminResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'admin_response' => fake()->paragraph(1),
            'admin_response_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ]);
    }
}
