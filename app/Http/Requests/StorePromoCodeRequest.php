<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPromoCode;
use App\Models\PromoCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePromoCodeRequest extends FormRequest
{
    use ValidatesPromoCode;

    public function authorize(): bool
    {
        return $this->user()->can('create', PromoCode::class);
    }

    protected function prepareForValidation(): void
    {
        $this->preparePromoCodeForValidation();

        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->promoCodeRules();
    }

    public function withValidator(Validator $validator): void
    {
        $this->withPromoCodeValidator($validator);
    }
}
