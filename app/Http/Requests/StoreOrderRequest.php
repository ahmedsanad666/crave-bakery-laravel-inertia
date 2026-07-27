<?php

namespace App\Http\Requests;

use App\Services\PaymentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
        $enabled = app(PaymentService::class)->enabledGatewayNames();

        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id),
                ),
            ],
            'email' => ['required', 'email', 'max:255'],
            'delivery_method' => ['required', Rule::in(['standard', 'express'])],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in($enabled ?: ['__none__'])],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.in' => 'The selected payment method is not available.',
        ];
    }
}
