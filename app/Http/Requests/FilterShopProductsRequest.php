<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterShopProductsRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::when(
                    $this->filled('price_min'),
                    ['gte:price_min'],
                ),
            ],
            'min_rating' => ['nullable', 'numeric', Rule::in([4])],
            'in_stock' => ['nullable', 'boolean'],
            'out_of_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['recommended', 'price_asc', 'price_desc', 'newest'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleans = ['in_stock', 'out_of_stock'];

        foreach ($booleans as $key) {
            if ($this->has($key) && $this->input($key) !== null && $this->input($key) !== '') {
                $this->merge([
                    $key => filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ]);
            }
        }

        if ($this->input('category_id') === '' || $this->input('category_id') === 'all') {
            $this->merge(['category_id' => null]);
        }

        if (! $this->filled('sort')) {
            $this->merge(['sort' => 'recommended']);
        }

        if (! $this->filled('per_page')) {
            $this->merge(['per_page' => 9]);
        }
    }
}
