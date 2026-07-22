<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const EXTRA_CUSTOMER_TARGET = 12;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@cravebakery.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => 'super_admin',
                'status' => 'active',
                'permissions' => null,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'manager@cravebakery.com'],
            [
                'name' => 'Store Manager',
                'password' => 'password',
                'role' => 'admin',
                'status' => 'active',
                'permissions' => AdminPermissions::fromTemplate('full_admin'),
                'email_verified_at' => now(),
            ],
        );

        $demoCustomer = User::query()->updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
                'role' => 'user',
                'status' => 'active',
                'permissions' => null,
                'email_verified_at' => now(),
            ],
        );

        Address::query()->updateOrCreate(
            [
                'user_id' => $demoCustomer->id,
                'is_default' => true,
            ],
            [
                'label' => 'Home',
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'phone' => fake()->phoneNumber(),
                'address_line1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'postal_code' => fake()->postcode(),
                'country' => 'US',
            ],
        );

        // Idempotent inactive customers for admin filter / stats testing.
        $inactiveProfiles = [
            [
                'email' => 'inactive.customer@example.com',
                'name' => 'Inactive Customer',
            ],
            [
                'email' => 'paused.customer@example.com',
                'name' => 'Paused Customer',
            ],
        ];

        foreach ($inactiveProfiles as $profile) {
            $customer = User::query()->updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => 'password',
                    'role' => 'user',
                    'status' => 'inactive',
                    'permissions' => null,
                    'email_verified_at' => now(),
                ],
            );

            Address::query()->updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'is_default' => true,
                ],
                [
                    'label' => 'Home',
                    'first_name' => explode(' ', $profile['name'])[0],
                    'last_name' => explode(' ', $profile['name'])[1] ?? 'Customer',
                    'phone' => fake()->phoneNumber(),
                    'address_line1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'US',
                ],
            );
        }

        $customerCount = User::query()->where('role', 'user')->count();
        $needed = max(0, self::EXTRA_CUSTOMER_TARGET + 1 - $customerCount);

        if ($needed === 0) {
            return;
        }

        User::factory($needed)->customer()->create()->each(function (User $user) {
            Address::factory()->default()->create([
                'user_id' => $user->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
            ]);
        });
    }
}
