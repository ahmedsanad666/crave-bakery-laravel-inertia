<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'type' => fake()->randomElement(['text', 'number', 'color', 'boolean']),
            'display_type' => fake()->randomElement(['pills', 'dropdown', 'swatches', 'checkboxes']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
