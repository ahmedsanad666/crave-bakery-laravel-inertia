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
            ['name' => 'Emma Wilson', 'email' => 'emma.wilson@example.com', 'phone' => '+15551001001'],
            ['name' => 'Liam Chen', 'email' => 'liam.chen@example.com', 'phone' => '+15551001002'],
            ['name' => 'Sofia Martinez', 'email' => 'sofia.martinez@example.com', 'phone' => '+15551001003'],
            ['name' => 'Noah Patel', 'email' => 'noah.patel@example.com', 'phone' => '+15551001004'],
            ['name' => 'Ava Johnson', 'email' => 'ava.johnson@example.com', 'phone' => '+15551001005'],
            ['name' => 'Mason Brooks', 'email' => 'mason.brooks@example.com', 'phone' => '+15551001006'],
            ['name' => 'Olivia Reed', 'email' => 'olivia.reed@example.com', 'phone' => '+15551001007'],
            ['name' => 'Ethan Torres', 'email' => 'ethan.torres@example.com', 'phone' => '+15551001008'],
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
                    'phone' => $customer['phone'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
