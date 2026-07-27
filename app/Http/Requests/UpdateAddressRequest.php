<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAddress;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    use ValidatesAddress;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('address')) ?? false;
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
