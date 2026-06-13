<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::query()->create([
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 25.00,
            'max_uses' => 500,
            'used_count' => 42,
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        PromoCode::query()->create([
            'code' => 'FREESHIP',
            'type' => 'fixed',
            'value' => 4.99,
            'min_order_amount' => 30.00,
            'max_uses' => 200,
            'used_count' => 18,
            'starts_at' => now()->subWeek(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);

        PromoCode::query()->create([
            'code' => 'BAKED15',
            'type' => 'percentage',
            'value' => 15,
            'min_order_amount' => 40.00,
            'max_uses' => 100,
            'used_count' => 7,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        PromoCode::factory(3)->create();
    }
}
