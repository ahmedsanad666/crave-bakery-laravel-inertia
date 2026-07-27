<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => [
                'nullable',
                Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $first = trim((string) $this->input('first_name', ''));
        $last = trim((string) $this->input('last_name', ''));

        $this->merge([
            'first_name' => $first !== '' ? $first : null,
            'last_name' => $last !== '' ? $last : null,
            'name' => trim($first.' '.$last),
            'phone' => filled($this->input('phone'))
                ? trim((string) $this->input('phone'))
                : null,
            'date_of_birth' => filled($this->input('date_of_birth'))
                ? $this->input('date_of_birth')
                : null,
            'gender' => filled($this->input('gender'))
                ? $this->input('gender')
                : null,
        ]);
    }
}
