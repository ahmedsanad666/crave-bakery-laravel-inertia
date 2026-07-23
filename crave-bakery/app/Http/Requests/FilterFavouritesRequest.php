<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterFavouritesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => [
                'nullable',
                'string',
                Rule::in(['newest', 'price_asc', 'price_desc', 'name']),
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('search') === '') {
            $this->merge(['search' => null]);
        }

        if ($this->input('category_id') === '' || $this->input('category_id') === 'all') {
            $this->merge(['category_id' => null]);
        }

        if ($this->input('sort') === '') {
            $this->merge(['sort' => null]);
        }
    }
}
