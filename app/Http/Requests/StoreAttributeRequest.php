<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAttribute;
use App\Models\Attribute;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
{
    use ValidatesAttribute;

    public function authorize(): bool
    {
        return $this->user()->can('create', Attribute::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->attributeRules();
    }
}
