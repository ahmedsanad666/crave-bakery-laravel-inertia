<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAttribute;
use App\Models\Attribute;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAttributeRequest extends FormRequest
{
    use ValidatesAttribute;

    public function authorize(): bool
    {
        /** @var Attribute $attribute */
        $attribute = $this->route('attribute');

        return $this->user()->can('update', $attribute);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->attributeRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Attribute $attribute */
            $attribute = $this->route('attribute');
            $valueIds = collect($this->input('values', []))
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id);

            if ($valueIds->isEmpty()) {
                return;
            }

            $ownedIds = $attribute->attributeValues()->pluck('id');
            $foreign = $valueIds->diff($ownedIds);

            if ($foreign->isNotEmpty()) {
                $validator->errors()->add(
                    'values',
                    'One or more values do not belong to this attribute.',
                );
            }
        });
    }
}
