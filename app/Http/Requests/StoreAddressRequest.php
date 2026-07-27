<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAddress;
use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    use ValidatesAddress;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Address::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->addressRules();
    }

    protected function prepareForValidation(): void
    {
        $this->prepareAddressValidation();
    }
}
