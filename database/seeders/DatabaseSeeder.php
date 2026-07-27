<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SiteSettingSeeder::class,
            PaymentGatewaySeeder::class,
            CategorySeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            AddressSeeder::class,
            PromoCodeSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
