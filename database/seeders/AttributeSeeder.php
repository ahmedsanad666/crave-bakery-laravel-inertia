<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Roast Level',
                'type' => 'text',
                'display_type' => 'pills',
                'sort_order' => 1,
                'values' => ['Light', 'Medium', 'Medium-Dark', 'Dark'],
            ],
            [
                'name' => 'Grind',
                'type' => 'text',
                'display_type' => 'dropdown',
                'sort_order' => 2,
                'values' => ['Whole Bean', 'Espresso', 'Filter', 'French Press', 'Cold Brew'],
            ],
            [
                'name' => 'Bag Size',
                'type' => 'text',
                'display_type' => 'pills',
                'sort_order' => 3,
                'values' => ['250g', '500g', '1kg'],
            ],
        ];

        foreach ($definitions as $definition) {
            $attribute = Attribute::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'type' => $definition['type'],
                    'display_type' => $definition['display_type'],
                    'sort_order' => $definition['sort_order'],
                ],
            );

            foreach ($definition['values'] as $i => $value) {
                AttributeValue::query()->updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ],
                    [
                        'color_swatch' => null,
                        'sort_order' => $i + 1,
                    ],
                );
            }
        }
    }
}
