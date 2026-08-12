<?php

namespace App\Http\Requests\Concerns;

use App\Models\PromoCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesPromoCode
{
    /**
     * @return array<string, mixed>
     */
    protected function promoCodeRules(?int $promoCodeId = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(PromoCode::class, 'code')->ignore($promoCodeId),
            ],
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable',
                'date',
                Rule::when(
                    filled($this->input('starts_at')),
                    ['after_or_equal:starts_at'],
                ),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function preparePromoCodeForValidation(): void
    {
        $code = $this->input('code');
        if (is_string($code)) {
            $this->merge(['code' => mb_strtoupper(trim($code))]);
        }

        foreach (['min_order_amount', 'max_uses', 'starts_at', 'expires_at'] as $field) {
            if ($this->input($field) === '' || $this->input($field) === 'null') {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }

    protected function withPromoCodeValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $value = $this->input('value');

            if ($type === 'percentage' && is_numeric($value) && (float) $value > 100) {
                $validator->errors()->add(
                    'value',
                    'Percentage discounts cannot exceed 100%.',
                );
            }
        });
    }
}
