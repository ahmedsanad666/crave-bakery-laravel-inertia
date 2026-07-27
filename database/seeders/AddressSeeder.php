<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'user')->orderBy('id')->get();

        $templates = [
            ['label' => 'Home', 'city' => 'Portland', 'state' => 'OR', 'postal_code' => '97209'],
            ['label' => 'Office', 'city' => 'Seattle', 'state' => 'WA', 'postal_code' => '98101'],
            ['label' => 'Home', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78701'],
            ['label' => 'Other', 'city' => 'Denver', 'state' => 'CO', 'postal_code' => '80202'],
        ];

        foreach ($customers as $i => $user) {
            $parts = explode(' ', $user->name, 2);
            $first = $parts[0];
            $last = $parts[1] ?? 'Customer';

            $primary = $templates[$i % count($templates)];

            Address::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'label' => $primary['label'],
                    'address_line1' => (100 + $i).' Brew Street',
                ],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone' => $user->phone ?: '+1555'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                    'address_line2' => $i % 2 === 0 ? 'Apt '.($i + 1) : null,
                    'city' => $primary['city'],
                    'state' => $primary['state'],
                    'postal_code' => $primary['postal_code'],
                    'country' => 'US',
                    'is_default' => true,
                ],
            );

            if ($i % 2 === 0) {
                $secondary = $templates[($i + 1) % count($templates)];

                Address::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'label' => 'Office',
                        'address_line1' => (200 + $i).' Roastery Ave',
                    ],
                    [
                        'first_name' => $first,
                        'last_name' => $last,
                        'phone' => $user->phone ?: '+1555'.str_pad((string) (2000000 + $i), 7, '0', STR_PAD_LEFT),
                        'address_line2' => 'Suite '.($i + 10),
                        'city' => $secondary['city'],
                        'state' => $secondary['state'],
                        'postal_code' => $secondary['postal_code'],
                        'country' => 'US',
                        'is_default' => false,
                    ],
                );
            }
        }
    }
}
