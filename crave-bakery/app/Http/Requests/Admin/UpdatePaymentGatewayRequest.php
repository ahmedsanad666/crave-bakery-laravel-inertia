<?php

namespace App\Http\Requests\Admin;

use App\Models\PaymentGatewayModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PaymentGatewayModel $gateway */
        $gateway = $this->route('gateway');

        return $this->user()->can('update', $gateway);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_enabled' => ['sometimes', 'boolean'],
            'is_test_mode' => ['sometimes', 'boolean'],
            'label' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'config' => ['nullable', 'array'],
            'config.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_enabled')) {
            $this->merge(['is_enabled' => filter_var($this->input('is_enabled'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('is_test_mode')) {
            $this->merge(['is_test_mode' => filter_var($this->input('is_test_mode'), FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
