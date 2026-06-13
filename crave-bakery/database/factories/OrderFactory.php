<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    private static int $orderSequence = 1;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 15, 180);
        $deliveryFee = fake()->randomElement([0, 4.99, 9.99]);
        $discount = fake()->boolean(20) ? round($subtotal * 0.1, 2) : 0;
        $tax = round(($subtotal - $discount) * 0.08, 2);
        $total = round($subtotal - $discount + $deliveryFee + $tax, 2);

        return [
            'user_id' => User::factory(),
            'order_number' => 'CB-'.str_pad((string) self::$orderSequence++, 5, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'payment_method' => fake()->randomElement(['card', 'paypal', 'apple_pay']),
            'transaction_id' => fake()->optional()->uuid(),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'promo_code' => null,
            'delivery_fee' => $deliveryFee,
            'tax_amount' => $tax,
            'total' => $total,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'country' => 'US',
            'delivery_method' => fake()->randomElement(['standard', 'express']),
            'delivery_notes' => fake()->optional()->sentence(),
            'estimated_delivery_at' => now()->addDays(fake()->numberBetween(1, 5)),
            'delivered_at' => null,
            'paid_at' => now()->subDays(fake()->numberBetween(0, 14)),
            'notes' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }
}
