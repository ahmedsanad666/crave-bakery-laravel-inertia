<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'phone' => fake()->phoneNumber(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'permissions' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'permissions' => [
                'products' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true, 'manage_stock' => true],
                'categories' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
                'orders' => ['view' => true, 'update_status' => true, 'refund' => true],
                'reviews' => ['view' => true, 'approve' => true, 'delete' => true, 'respond' => true],
                'customers' => ['view' => true, 'edit' => true, 'export' => true],
            ],
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'permissions' => null,
        ]);
    }
}
