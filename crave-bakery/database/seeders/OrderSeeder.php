<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Demo order profiles: mixed statuses for admin stats / UI later.
     *
     * @var list<array{status: string, payment_status: string}>
     */
    private const ORDER_PROFILES = [
        ['status' => 'pending', 'payment_status' => 'pending'],
        ['status' => 'processing', 'payment_status' => 'paid'],
        ['status' => 'processing', 'payment_status' => 'paid'],
        ['status' => 'shipped', 'payment_status' => 'paid'],
        ['status' => 'shipped', 'payment_status' => 'paid'],
        ['status' => 'delivered', 'payment_status' => 'paid'],
        ['status' => 'delivered', 'payment_status' => 'paid'],
        ['status' => 'delivered', 'payment_status' => 'refunded'],
        ['status' => 'cancelled', 'payment_status' => 'pending'],
        ['status' => 'cancelled', 'payment_status' => 'refunded'],
    ];

    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->get();

        if ($customers->isEmpty()) {
            $customers = $this->ensureCustomers();
        }

        $products = Product::query()->where('status', 'active')->get();

        // Products must be seeded first (CategorySeeder → ProductSeeder).
        if ($products->isEmpty()) {
            $this->command?->warn('OrderSeeder skipped: no active products found.');

            return;
        }

        foreach (self::ORDER_PROFILES as $index => $profile) {
            $orderNumber = 'CB-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);

            if (Order::query()->where('order_number', $orderNumber)->exists()) {
                continue;
            }

            $customer = $customers[$index % $customers->count()];
            $address = $customer->addresses()->first();
            $itemCount = fake()->numberBetween(1, 3);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            if (! $selectedProducts instanceof \Illuminate\Support\Collection) {
                $selectedProducts = collect([$selectedProducts]);
            }

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
            $isDelivered = $profile['status'] === 'delivered';
            $isPaid = in_array($profile['payment_status'], ['paid', 'refunded'], true);

            $order = Order::query()->create([
                'user_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => $profile['status'],
                'payment_status' => $profile['payment_status'],
                'payment_method' => fake()->randomElement(['card', 'paypal', 'apple_pay']),
                'transaction_id' => $isPaid ? fake()->uuid() : null,
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
                'estimated_delivery_at' => now()->addDays(fake()->numberBetween(1, 5)),
                'delivered_at' => $isDelivered
                    ? now()->subDays(fake()->numberBetween(1, 10))
                    : null,
                'paid_at' => $isPaid
                    ? now()->subDays(fake()->numberBetween(1, 14))
                    : null,
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function ensureCustomers()
    {
        $users = collect();

        for ($i = 1; $i <= 5; $i++) {
            $user = User::query()->updateOrCreate(
                ['email' => "order-customer-{$i}@example.com"],
                [
                    'name' => "Order Customer {$i}",
                    'password' => 'password',
                    'role' => 'user',
                    'permissions' => null,
                    'email_verified_at' => now(),
                ],
            );

            Address::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'is_default' => true,
                ],
                [
                    'label' => 'Home',
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'phone' => fake()->phoneNumber(),
                    'address_line1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'US',
                ],
            );

            $users->push($user);
        }

        return $users;
    }
}
