<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->get();
        $products = Product::query()->where('status', 'active')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $orderNumber = 1;

        foreach ($customers->take(8) as $customer) {
            $address = $customer->addresses()->first();
            $itemCount = fake()->numberBetween(1, 3);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subtotal = 0;
            $lineItems = [];

            foreach ($selectedProducts as $product) {
                $quantity = fake()->numberBetween(1, 3);
                $unitPrice = (float) ($product->sale_price ?? $product->regular_price);
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $deliveryFee = $subtotal >= 30 ? 0 : 4.99;
            $tax = round($subtotal * 0.08, 2);
            $total = round($subtotal + $deliveryFee + $tax, 2);

            $order = Order::query()->create([
                'user_id' => $customer->id,
                'order_number' => 'CB-'.str_pad((string) $orderNumber++, 5, '0', STR_PAD_LEFT),
                'status' => fake()->randomElement(['processing', 'shipped', 'delivered', 'delivered']),
                'payment_status' => 'paid',
                'payment_method' => fake()->randomElement(['card', 'paypal']),
                'transaction_id' => fake()->uuid(),
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'promo_code' => null,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $tax,
                'total' => $total,
                'first_name' => $address?->first_name ?? fake()->firstName(),
                'last_name' => $address?->last_name ?? fake()->lastName(),
                'email' => $customer->email,
                'phone' => $address?->phone ?? fake()->phoneNumber(),
                'address_line1' => $address?->address_line1 ?? fake()->streetAddress(),
                'address_line2' => $address?->address_line2,
                'city' => $address?->city ?? fake()->city(),
                'state' => $address?->state ?? fake()->stateAbbr(),
                'postal_code' => $address?->postal_code ?? fake()->postcode(),
                'country' => $address?->country ?? 'US',
                'delivery_method' => fake()->randomElement(['standard', 'express']),
                'estimated_delivery_at' => now()->addDays(2),
                'delivered_at' => fake()->optional(0.6)->dateTimeBetween('-10 days', '-1 day'),
                'paid_at' => now()->subDays(fake()->numberBetween(1, 14)),
            ]);

            foreach ($lineItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'selected_attributes' => null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);
            }
        }
    }
}
