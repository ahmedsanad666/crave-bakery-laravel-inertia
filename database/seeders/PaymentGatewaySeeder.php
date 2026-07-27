<?php

namespace Database\Seeders;

use App\Models\PaymentGatewayModel;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        PaymentGatewayModel::query()->updateOrCreate(
            ['name' => 'stripe'],
            [
                'label' => 'Credit / Debit Card',
                'description' => 'Pay securely with your Visa, Mastercard, or American Express card.',
                'logo' => null,
                'is_enabled' => true,
                'is_test_mode' => true,
                'config' => [
                    'key' => env('STRIPE_KEY', ''),
                    'secret' => env('STRIPE_SECRET', ''),
                    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
                ],
                'instructions' => null,
                'sort_order' => 1,
            ],
        );

        PaymentGatewayModel::query()->updateOrCreate(
            ['name' => 'cod'],
            [
                'label' => 'Cash on Delivery',
                'description' => 'Pay with cash when your order arrives at your door.',
                'logo' => null,
                'is_enabled' => true,
                'is_test_mode' => false,
                'config' => null,
                'instructions' => 'Please have the exact amount ready. Our delivery team will collect payment upon arrival.',
                'sort_order' => 2,
            ],
        );
    }
}
