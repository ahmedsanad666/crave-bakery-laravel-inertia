<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
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

        $customers = [
            ['name' => 'Emma Wilson', 'email' => 'emma.wilson@example.com'],
            ['name' => 'Liam Chen', 'email' => 'liam.chen@example.com'],
            ['name' => 'Sofia Martinez', 'email' => 'sofia.martinez@example.com'],
            ['name' => 'Noah Patel', 'email' => 'noah.patel@example.com'],
            ['name' => 'Ava Johnson', 'email' => 'ava.johnson@example.com'],
            ['name' => 'Mason Brooks', 'email' => 'mason.brooks@example.com'],
            ['name' => 'Olivia Reed', 'email' => 'olivia.reed@example.com'],
            ['name' => 'Ethan Torres', 'email' => 'ethan.torres@example.com'],
        ];

        foreach ($customers as $customer) {
            User::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => 'password',
                    'role' => 'user',
                    'status' => 'active',
                    'permissions' => null,
                    'phone' => '+1'.fake()->numerify('##########'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
