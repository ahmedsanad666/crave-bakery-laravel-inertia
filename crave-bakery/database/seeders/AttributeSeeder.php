<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAttribute('Size', 'text', 'pills', 1, [
            'Individual',
            'Box of 4',
            'Box of 6',
            'Large',
        ]);

        $this->seedAttribute('Flavor', 'text', 'dropdown', 2, [
            'Classic',
            'Chocolate',
            'Vanilla',
            'Strawberry',
            'Lemon',
        ]);

        $this->seedAttribute('Glaze', 'color', 'swatches', 3, [
            ['value' => 'Plain', 'color_swatch' => '#FDF6EE'],
            ['value' => 'Honey', 'color_swatch' => '#EF9F27'],
            ['value' => 'Chocolate', 'color_swatch' => '#3D1A0E'],
            ['value' => 'Berry', 'color_swatch' => '#C62828'],
        ], withColor: true);
    }

    /**
     * @param  list<string|array{value: string, color_swatch: string}>  $values
     */
    private function seedAttribute(
        string $name,
        string $type,
        string $displayType,
        int $sortOrder,
        array $values,
        bool $withColor = false,
    ): void {
        $attribute = Attribute::query()->updateOrCreate(
            ['name' => $name],
            [
                'type' => $type,
                'display_type' => $displayType,
                'sort_order' => $sortOrder,
            ],
        );

        foreach ($values as $index => $value) {
            $payload = $withColor
                ? [
                    'value' => $value['value'],
                    'color_swatch' => $value['color_swatch'],
                    'sort_order' => $index + 1,
                ]
                : [
                    'value' => $value,
                    'color_swatch' => null,
                    'sort_order' => $index + 1,
                ];

            AttributeValue::query()->updateOrCreate(
                [
                    'attribute_id' => $attribute->id,
                    'value' => $payload['value'],
                ],
                [
                    'color_swatch' => $payload['color_swatch'],
                    'sort_order' => $payload['sort_order'],
                ],
            );
        }
    }
}
