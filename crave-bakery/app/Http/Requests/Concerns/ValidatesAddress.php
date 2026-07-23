<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesAddress
{
    /**
     * @return array<string, mixed>
     */
    protected function addressRules(): array
    {
        return [
            'label' => ['required', Rule::in(['Home', 'Office', 'Other'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareAddressValidation(): void
    {
        $nullable = ['phone', 'address_line2', 'state'];

        foreach ($nullable as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }

        if (! $this->has('is_default')) {
            $this->merge(['is_default' => false]);
        } else {
            $this->merge([
                'is_default' => filter_var(
                    $this->input('is_default'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ) ?? false,
            ]);
        }
    }
}
