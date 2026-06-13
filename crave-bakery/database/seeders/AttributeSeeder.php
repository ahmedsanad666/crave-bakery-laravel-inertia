<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $size = Attribute::query()->create([
            'name' => 'Size',
            'type' => 'text',
            'display_type' => 'pills',
            'sort_order' => 1,
        ]);

        foreach (['Individual', 'Box of 4', 'Box of 6', 'Large'] as $index => $value) {
            AttributeValue::query()->create([
                'attribute_id' => $size->id,
                'value' => $value,
                'sort_order' => $index + 1,
            ]);
        }

        $flavor = Attribute::query()->create([
            'name' => 'Flavor',
            'type' => 'text',
            'display_type' => 'dropdown',
            'sort_order' => 2,
        ]);

        foreach (['Classic', 'Chocolate', 'Vanilla', 'Strawberry', 'Lemon'] as $index => $value) {
            AttributeValue::query()->create([
                'attribute_id' => $flavor->id,
                'value' => $value,
                'sort_order' => $index + 1,
            ]);
        }

        $glaze = Attribute::query()->create([
            'name' => 'Glaze',
            'type' => 'color',
            'display_type' => 'swatches',
            'sort_order' => 3,
        ]);

        $glazeOptions = [
            ['value' => 'Plain', 'color_swatch' => '#FDF6EE'],
            ['value' => 'Honey', 'color_swatch' => '#EF9F27'],
            ['value' => 'Chocolate', 'color_swatch' => '#3D1A0E'],
            ['value' => 'Berry', 'color_swatch' => '#C62828'],
        ];

        foreach ($glazeOptions as $index => $option) {
            AttributeValue::query()->create([
                'attribute_id' => $glaze->id,
                'value' => $option['value'],
                'color_swatch' => $option['color_swatch'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
