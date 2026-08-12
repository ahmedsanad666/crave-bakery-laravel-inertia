<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPromoCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePromoCodeRequest extends FormRequest
{
    use ValidatesPromoCode;

    public function authorize(): bool
    {
        $promoCode = $this->route('promo_code');

        return $this->user()->can('update', $promoCode);
    }

    protected function prepareForValidation(): void
    {
        $this->preparePromoCodeForValidation();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $promoCode = $this->route('promo_code');

        return $this->promoCodeRules($promoCode->id);
    }

    public function withValidator(Validator $validator): void
    {
        $this->withPromoCodeValidator($validator);
    }
}
