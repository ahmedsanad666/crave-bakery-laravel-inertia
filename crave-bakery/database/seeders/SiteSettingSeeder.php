<?php

namespace Database\Seeders;

use App\Services\SiteSettingService;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'name' => 'Crave Bakery',
            'tagline' => 'Baking Smiles, One Pastry At A Time',
            'about' => 'Artisan bakery crafting fresh pastries, cakes, and breads daily with premium ingredients.',
            'email' => 'hello@cravebakery.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Baker Street, Sweetville, CA 90210',
            'logo' => null,
            'favicon' => null,
        ];

        foreach ($settings as $key => $value) {
            SiteSettingService::set($key, $value);
        }
    }
}
