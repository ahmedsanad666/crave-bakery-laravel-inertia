<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesAttribute
{
    protected function attributeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['text', 'number', 'color', 'boolean'])],
            'display_type' => ['required', Rule::in(['pills', 'dropdown', 'swatches', 'checkboxes'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer', 'exists:attribute_values,id'],
            'values.*.value' => ['required', 'string', 'max:255'],
            'values.*.color_swatch' => [
                Rule::requiredIf(fn () => $this->input('type') === 'color'),
                'nullable',
                'string',
                'max:50',
            ],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
