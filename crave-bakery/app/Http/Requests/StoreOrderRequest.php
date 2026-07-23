<?php

namespace App\Http\Requests;

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
            'payment_method' => ['required', Rule::in(['cod'])],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_method' => 'cod',
        ]);
    }
}
