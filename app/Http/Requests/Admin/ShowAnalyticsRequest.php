<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->missing('period')) {
            $this->merge(['period' => '1m']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'string', Rule::in(['7d', '1m', '3m', '1y'])],
        ];
    }
}
