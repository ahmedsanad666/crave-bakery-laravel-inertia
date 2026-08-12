<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'code' => strtoupper(Str::random(8)),
            'title' => fake()->sentence(4),
            'type' => $type,
            'value' => $type === 'percentage' ? fake()->numberBetween(5, 25) : fake()->randomFloat(2, 5, 20),
            'min_order_amount' => fake()->optional()->randomFloat(2, 20, 50),
            'max_uses' => fake()->optional()->numberBetween(50, 500),
            'used_count' => fake()->numberBetween(0, 20),
            'starts_at' => now()->subDays(7),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ];
    }
}
