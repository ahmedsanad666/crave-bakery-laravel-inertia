<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->orderBy('id')->get();
        $products = Product::query()->where('status', 'active')->inRandomOrder()->limit(40)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = [
            ['status' => 'pending', 'payment_status' => 'pending', 'delivered' => false],
            ['status' => 'processing', 'payment_status' => 'paid', 'delivered' => false],
            ['status' => 'shipped', 'payment_status' => 'paid', 'delivered' => false],
            ['status' => 'delivered', 'payment_status' => 'paid', 'delivered' => true],
            ['status' => 'delivered', 'payment_status' => 'paid', 'delivered' => true],
            ['status' => 'cancelled', 'payment_status' => 'refunded', 'delivered' => false],
        ];

        DB::transaction(function () use ($customers, $products, $statuses) {
            for ($i = 1; $i <= 25; $i++) {
                $user = $customers[($i - 1) % $customers->count()];
                $address = Address::query()->where('user_id', $user->id)->where('is_default', true)->first()
                    ?? Address::query()->where('user_id', $user->id)->first();
                $state = $statuses[($i - 1) % count($statuses)];
                $orderNumber = 'CS-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);

                $lineProducts = $products->random(random_int(1, 4));
                $subtotal = 0;
                $lines = [];

                foreach ($lineProducts as $product) {
                    $qty = random_int(1, 3);
                    $unit = (float) ($product->sale_price ?? $product->regular_price);
                    $lineTotal = round($unit * $qty, 2);
                    $subtotal += $lineTotal;
                    $lines[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'line_total' => $lineTotal,
                    ];
                }

                $discount = $i % 5 === 0 ? round($subtotal * 0.1, 2) : 0;
                $delivery = $state['status'] === 'cancelled' ? 0 : ($subtotal >= 50 ? 0 : 5.99);
                $tax = round(($subtotal - $discount) * 0.08, 2);
                $total = round($subtotal - $discount + $delivery + $tax, 2);

                $nameParts = explode(' ', $user->name, 2);

                $order = Order::query()->updateOrCreate(
                    ['order_number' => $orderNumber],
                    [
                        'user_id' => $user->id,
                        'status' => $state['status'],
                        'payment_status' => $state['payment_status'],
                        'payment_method' => $i % 3 === 0 ? 'cod' : 'stripe',
                        'transaction_id' => $state['payment_status'] === 'paid' ? 'txn_seed_'.$i : null,
                        'subtotal' => $subtotal,
                        'discount_amount' => $discount,
                        'promo_code' => $discount > 0 ? 'COFFEE10' : null,
                        'delivery_fee' => $delivery,
                        'tax_amount' => $tax,
                        'total' => $total,
                        'first_name' => $address?->first_name ?? $nameParts[0],
                        'last_name' => $address?->last_name ?? ($nameParts[1] ?? 'Customer'),
                        'email' => $user->email,
                        'phone' => $address?->phone ?? $user->phone,
                        'address_line1' => $address?->address_line1 ?? '100 Brew Street',
                        'address_line2' => $address?->address_line2,
                        'city' => $address?->city ?? 'Portland',
                        'state' => $address?->state ?? 'OR',
                        'postal_code' => $address?->postal_code ?? '97209',
                        'country' => $address?->country ?? 'US',
                        'delivery_method' => $i % 4 === 0 ? 'express' : 'standard',
                        'delivery_notes' => $i % 6 === 0 ? 'Leave at the front desk' : null,
                        'estimated_delivery_at' => now()->addDays(random_int(2, 6)),
                        'delivered_at' => $state['delivered'] ? now()->subDays(random_int(1, 20)) : null,
                        'paid_at' => $state['payment_status'] === 'paid' ? now()->subDays(random_int(1, 25)) : null,
                        'notes' => null,
                        'created_at' => now()->subDays(random_int(1, 45)),
                    ],
                );

                OrderItem::query()->where('order_id', $order->id)->delete();

                foreach ($lines as $line) {
                    /** @var Product $product */
                    $product = $line['product'];

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'selected_attributes' => [
                            'Roast Level' => 'Medium',
                            'Grind' => 'Whole Bean',
                        ],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                    ]);
                }
            }
        });
    }
}
