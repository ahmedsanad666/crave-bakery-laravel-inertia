<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@cravebakery.com',
        ]);

        User::factory()->admin()->create([
            'name' => 'Store Manager',
            'email' => 'manager@cravebakery.com',
        ]);

        $demoCustomer = User::factory()->customer()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
        ]);

        Address::factory()->default()->create([
            'user_id' => $demoCustomer->id,
            'first_name' => 'Demo',
            'last_name' => 'Customer',
        ]);

        User::factory(12)->customer()->create()->each(function (User $user) {
            Address::factory()->default()->create([
                'user_id' => $user->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
            ]);
        });
    }
}
