<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function selectedAttributeMap(): array
    {
        $attributes = $this->validated('attributes') ?? [];

        $map = [];

        foreach ($attributes as $attributeId => $valueId) {
            $map[(int) $attributeId] = (int) $valueId;
        }

        return $map;
    }
}
