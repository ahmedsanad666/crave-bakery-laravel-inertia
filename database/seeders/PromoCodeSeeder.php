<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            [
                'code' => 'WELCOME15',
                'title' => '15% off your first order',
                'type' => 'percentage',
                'value' => 15,
                'min_order_amount' => 25,
                'max_uses' => 500,
                'used_count' => 42,
            ],
            [
                'code' => 'COFFEE10',
                'title' => '10% off coffee pairings',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 20,
                'max_uses' => 1000,
                'used_count' => 188,
            ],
            [
                'code' => 'FREESHIP',
                'title' => 'Delivery fee on us',
                'type' => 'fixed',
                'value' => 5.99,
                'min_order_amount' => 40,
                'max_uses' => 300,
                'used_count' => 67,
            ],
            [
                'code' => 'ESPRESSO5',
                'title' => '$5 off espresso treats',
                'type' => 'fixed',
                'value' => 5,
                'min_order_amount' => 30,
                'max_uses' => 200,
                'used_count' => 23,
            ],
            [
                'code' => 'BREW20',
                'title' => '20% off weekend bakery box',
                'type' => 'percentage',
                'value' => 20,
                'min_order_amount' => 60,
                'max_uses' => 100,
                'used_count' => 11,
            ],
        ];

        foreach ($codes as $code) {
            PromoCode::query()->updateOrCreate(
                ['code' => $code['code']],
                [
                    'title' => $code['title'],
                    'type' => $code['type'],
                    'value' => $code['value'],
                    'min_order_amount' => $code['min_order_amount'],
                    'max_uses' => $code['max_uses'],
                    'used_count' => $code['used_count'],
                    'starts_at' => now()->subDays(30),
                    'expires_at' => now()->addMonths(4),
                    'is_active' => true,
                ],
            );
        }
    }
}
