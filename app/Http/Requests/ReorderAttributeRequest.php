<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('attributes', 'edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct', 'exists:attributes,id'],
        ];
    }
}
